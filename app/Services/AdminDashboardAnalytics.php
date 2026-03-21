<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\StudentCourse;
use App\Models\StudentQuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Métriques réelles pour le tableau de bord admin (graphiques + KPIs).
 *
 * « Membres actifs » : nombre de comptes distincts ayant eu une session en base
 * ce jour / cette semaine / ce mois (SESSION_DRIVER=database). Sinon 0 avec note.
 */
final class AdminDashboardAnalytics
{
    /** @return array<string, mixed> */
    public function payload(): array
    {
        return [
            'periods' => [
                '30j' => $this->buildPeriod(30, 'day'),
                '3m' => $this->buildPeriod(90, 'week'),
                '1y' => $this->buildPeriod(365, 'month'),
            ],
            'uses_database_sessions' => config('session.driver') === 'database',
            'recent_users' => $this->recentUsers(),
            'recent_invoices' => $this->recentInvoices(),
        ];
    }

    /**
     * @return array{charts: array<string, array{title: string, hint: string, labels: list<string>, values: list<float|int>}>, kpis: array<string, mixed>}
     */
    private function buildPeriod(int $daysBack, string $granularity): array
    {
        $end = now()->timezone(config('app.timezone'))->endOfDay();
        $start = now()->timezone(config('app.timezone'))->subDays($daysBack)->startOfDay();

        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($daysBack)->startOfDay();

        $keys = $this->orderedBucketKeys($start, $end, $granularity);

        $visitorsRaw = $this->distinctSessionUsersByBucket($start, $end, $granularity);
        $signupsRaw = $this->countUsersCreatedByBucket($start, $end, $granularity);
        $revenueRaw = $this->sumPaidInvoicesTtcByBucket($start, $end, $granularity);
        $quizRaw = $this->countQuizAttemptsByBucket($start, $end, $granularity);

        $labels = array_map(fn (string $k) => $this->humanLabel($granularity, $k), $keys);

        $revenueCurrent = array_sum(array_intersect_key($revenueRaw, array_flip($keys)));
        $revenuePrev = $this->totalPaidRevenueBetween($prevStart, $prevEnd);

        $charts = [
            'visitors' => [
                'title' => 'Membres actifs distincts',
                'hint' => 'Comptes uniques avec session en base sur la période (pas plusieurs fois le même jour).',
                'labels' => $labels,
                'values' => array_map(fn (string $k) => (int) ($visitorsRaw[$k] ?? 0), $keys),
            ],
            'signups' => [
                'title' => 'Nouveaux inscrits',
                'hint' => 'Comptes créés (date d’inscription).',
                'labels' => $labels,
                'values' => array_map(fn (string $k) => (int) ($signupsRaw[$k] ?? 0), $keys),
            ],
            'revenue' => [
                'title' => 'Chiffre d’affaires encaissé',
                'hint' => 'Factures au statut « payée », montant TTC par date de paiement.',
                'labels' => $labels,
                'values' => array_map(fn (string $k) => round((float) ($revenueRaw[$k] ?? 0), 2), $keys),
            ],
            'quiz' => [
                'title' => 'Activité cours (quiz)',
                'hint' => 'Tentatives de quiz terminées (engagement sur le contenu pédagogique).',
                'labels' => $labels,
                'values' => array_map(fn (string $k) => (int) ($quizRaw[$k] ?? 0), $keys),
            ],
        ];

        $newUsersInPeriod = User::query()->whereBetween('created_at', [$start, $end])->count();

        $kpis = [
            'revenue_ttc' => round($revenueCurrent, 2),
            'revenue_delta_pct' => $this->deltaPercent($revenueCurrent, $revenuePrev),
            'active_courses' => StudentCourse::query()
                ->whereIn('status', ['planned', 'in_progress'])
                ->count(),
            'completed_courses' => StudentCourse::query()->where('status', 'completed')->count(),
            'users_total' => User::query()->count(),
            'portal_users' => User::query()
                ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['client', 'student']))
                ->count(),
            'new_users_period' => $newUsersInPeriod,
        ];

        return [
            'charts' => $charts,
            'kpis' => $kpis,
            'range_label' => match ($granularity) {
                'day' => 'Un point par jour · '.$daysBack.' jours',
                'week' => 'Un point par semaine ISO · fenêtre '.$daysBack.' j.',
                'month' => 'Un point par mois · fenêtre '.$daysBack.' j.',
                default => '',
            },
        ];
    }

    private function deltaPercent(float $current, float $previous): ?float
    {
        if ($previous <= 0.0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function totalPaidRevenueBetween(Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('invoices')) {
            return 0.0;
        }

        $sum = 0.0;
        Invoice::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start->toDateString(), $end->toDateString()])
            ->cursor()
            ->each(function (Invoice $inv) use (&$sum): void {
                $sum += $inv->amountTtc();
            });

        return $sum;
    }

    /**
     * @return array<string, float|int>
     */
    private function sumPaidInvoicesTtcByBucket(Carbon $start, Carbon $end, string $granularity): array
    {
        if (! Schema::hasTable('invoices')) {
            return [];
        }

        $out = [];
        Invoice::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start->toDateString(), $end->toDateString()])
            ->cursor()
            ->each(function (Invoice $inv) use (&$out, $granularity): void {
                $paid = $inv->paid_at;
                if ($paid === null) {
                    return;
                }
                $c = $paid instanceof Carbon ? $paid : Carbon::parse($paid, config('app.timezone'));
                $k = $this->bucketKey($c, $granularity);
                $out[$k] = ($out[$k] ?? 0) + $inv->amountTtc();
            });

        return $out;
    }

    /**
     * @return array<string, int>
     */
    private function countUsersCreatedByBucket(Carbon $start, Carbon $end, string $granularity): array
    {
        $out = [];
        User::query()
            ->whereBetween('created_at', [$start, $end])
            ->cursor()
            ->each(function (User $u) use (&$out, $granularity): void {
                $k = $this->bucketKey($u->created_at, $granularity);
                $out[$k] = ($out[$k] ?? 0) + 1;
            });

        return $out;
    }

    /**
     * @return array<string, int>
     */
    private function countQuizAttemptsByBucket(Carbon $start, Carbon $end, string $granularity): array
    {
        if (! Schema::hasTable('student_quiz_attempts')) {
            return [];
        }

        $out = [];
        StudentQuizAttempt::query()
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->cursor()
            ->each(function (StudentQuizAttempt $a) use (&$out, $granularity): void {
                $k = $this->bucketKey($a->completed_at, $granularity);
                $out[$k] = ($out[$k] ?? 0) + 1;
            });

        return $out;
    }

    /**
     * @return array<string, int> bucket => distinct user count
     */
    private function distinctSessionUsersByBucket(Carbon $start, Carbon $end, string $granularity): array
    {
        if (config('session.driver') !== 'database' || ! Schema::hasTable('sessions')) {
            return [];
        }

        /** @var array<string, array<int, true>> $nested */
        $nested = [];

        foreach (
            DB::table('sessions')
                ->whereNotNull('user_id')
                ->whereBetween('last_activity', [$start->timestamp, $end->timestamp])
                ->cursor() as $row
        ) {
            $ts = (int) $row->last_activity;
            $c = Carbon::createFromTimestamp($ts, config('app.timezone'));
            $k = $this->bucketKey($c, $granularity);
            $uid = (int) $row->user_id;
            $nested[$k][$uid] = true;
        }

        $out = [];
        foreach ($nested as $k => $ids) {
            $out[$k] = count($ids);
        }

        return $out;
    }

    private function bucketKey(Carbon $dt, string $granularity): string
    {
        $dt = $dt->copy()->timezone(config('app.timezone'));

        return match ($granularity) {
            'day' => $dt->format('Y-m-d'),
            'week' => $dt->isoWeekYear.'-W'.str_pad((string) $dt->isoWeek, 2, '0', STR_PAD_LEFT),
            'month' => $dt->format('Y-m'),
            default => $dt->format('Y-m-d'),
        };
    }

    /**
     * @return list<string>
     */
    private function orderedBucketKeys(Carbon $start, Carbon $end, string $granularity): array
    {
        $seen = [];
        $keys = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $k = $this->bucketKey($cursor, $granularity);
            if (! isset($seen[$k])) {
                $seen[$k] = true;
                $keys[] = $k;
            }
            $cursor->addDay();
        }

        return $keys;
    }

    private function humanLabel(string $granularity, string $key): string
    {
        if ($granularity === 'day') {
            return Carbon::parse($key, config('app.timezone'))
                ->locale('fr')
                ->isoFormat('D MMM');
        }
        if ($granularity === 'week') {
            if (preg_match('/^(\d{4})-W(\d{2})$/', $key, $m)) {
                return 'S'.$m[2].' '.$m[1];
            }

            return $key;
        }
        if ($granularity === 'month') {
            return Carbon::parse($key.'-01', config('app.timezone'))
                ->locale('fr')
                ->isoFormat('MMM YYYY');
        }

        return $key;
    }

    /**
     * @return list<array{name: string, email: string, created_at: string|null}>
     */
    private function recentUsers(): array
    {
        return User::query()
            ->latest()
            ->limit(8)
            ->get(['name', 'email', 'created_at'])
            ->map(fn (User $u) => [
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at?->timezone(config('app.timezone'))->translatedFormat('d MMM Y, H:i'),
            ])
            ->all();
    }

    /**
     * @return list<array{number: string, label: string|null, status: string, amount_ttc: float, paid_at: string|null}>
     */
    private function recentInvoices(): array
    {
        if (! Schema::hasTable('invoices')) {
            return [];
        }

        return Invoice::query()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Invoice $inv) => [
                'number' => $inv->number,
                'label' => $inv->label,
                'status' => $inv->statusLabel(),
                'amount_ttc' => round($inv->amountTtc(), 2),
                'paid_at' => $inv->paid_at?->translatedFormat('d MMM Y'),
            ])
            ->all();
    }
}
