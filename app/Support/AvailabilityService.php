<?php

namespace App\Support;

use App\Models\AvailabilitySetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    private ?AvailabilitySetting $settings = null;

    public function settings(): AvailabilitySetting
    {
        return $this->settings ??= AvailabilitySetting::current();
    }

    private function tz(): string
    {
        return (string) config('app.timezone');
    }

    /**
     * Génère tous les créneaux réservables théoriques (selon la règle par
     * défaut) entre maintenant et l'horizon configuré.
     *
     * @return Collection<int, array{start: Carbon, end: Carbon}>
     */
    public function candidates(): Collection
    {
        $s = $this->settings();
        $out = collect();

        if (! $s->active) {
            return $out;
        }

        $tz = $this->tz();
        $now = Carbon::now($tz);
        $limit = $now->copy()->addWeeks($s->horizon_weeks)->endOfDay();
        $weekdays = array_map('intval', (array) $s->weekdays);
        $duration = max(5, (int) $s->slot_minutes);

        $day = $now->copy()->startOfDay();
        while ($day <= $limit) {
            if (in_array($day->isoWeekday(), $weekdays, true)) {
                $cursor = $this->applyTime($day, $s->start_time);
                $dayEnd = $this->applyTime($day, $s->end_time);

                while ($cursor->copy()->addMinutes($duration) <= $dayEnd) {
                    $end = $cursor->copy()->addMinutes($duration);
                    if ($cursor->gt($now)) {
                        $out->push(['start' => $cursor->copy(), 'end' => $end->copy()]);
                    }
                    $cursor = $end;
                }
            }
            $day->addDay();
        }

        return $out;
    }

    /**
     * Vérifie qu'un couple début/fin correspond exactement à la grille de la
     * règle par défaut (utilisé pour valider une réservation côté serveur).
     */
    public function isScheduledSlot(Carbon $start, Carbon $end): bool
    {
        $s = $this->settings();

        if (! $s->active) {
            return false;
        }

        $weekdays = array_map('intval', (array) $s->weekdays);
        if (! in_array($start->isoWeekday(), $weekdays, true)) {
            return false;
        }

        $duration = (int) $s->slot_minutes;
        if ((int) round($start->diffInMinutes($end)) !== $duration || $duration <= 0) {
            return false;
        }

        if ($start->format('H:i') < $s->start_time || $end->format('H:i') > $s->end_time) {
            return false;
        }

        $windowStart = $this->applyTime($start, $s->start_time);
        $offset = (int) round($windowStart->diffInMinutes($start));

        return $offset >= 0 && $offset % $duration === 0;
    }

    private function applyTime(Carbon $day, string $time): Carbon
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        return $day->copy()->setTime($h, $m, 0);
    }
}
