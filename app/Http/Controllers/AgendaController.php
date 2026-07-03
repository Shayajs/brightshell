<?php

namespace App\Http\Controllers;

use App\Models\AppointmentBusyBlock;
use App\Models\AppointmentSlot;
use App\Models\AvailabilitySetting;
use App\Support\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AgendaController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    /**
     * Point d'entrée unique du sous-domaine agenda.* :
     * un admin connecté gère les créneaux, tout autre visiteur voit le
     * calendrier public de réservation.
     */
    public function index(Request $request): View
    {
        if ($this->isAdmin($request)) {
            return view('agenda.admin', $this->buildSlots(true) + [
                'settings' => AvailabilitySetting::current(),
            ]);
        }

        return view('agenda.visitor', $this->buildSlots(false) + [
            'previewMode' => false,
        ]);
    }

    /**
     * Aperçu de la version visiteur, réservé aux admins connectés (/visite).
     */
    public function preview(Request $request): View|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('agenda.index');
        }

        return view('agenda.visitor', $this->buildSlots(false) + [
            'previewMode' => true,
        ]);
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && ($user->hasRole('admin') || $user->isAdmin());
    }

    /**
     * Construit la liste des créneaux affichés : créneaux virtuels générés par
     * la règle de disponibilité par défaut, fusionnés avec les créneaux
     * explicites (réservés, bloqués, ajouts manuels), moins les indisponibilités.
     *
     * @return array{slots: array<int, array<string, mixed>>, busy: array<int, array<string, mixed>>}
     */
    private function buildSlots(bool $forAdmin): array
    {
        $tz = (string) config('app.timezone');
        $now = now();

        $busy = AppointmentBusyBlock::query()
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        $explicit = AppointmentSlot::query()
            ->with('booking')
            ->where('starts_at', '>=', $forAdmin ? $now->copy()->subMonth() : $now)
            ->orderBy('starts_at')
            ->get();

        $occupied = $explicit->whereIn('status', [
            AppointmentSlot::STATUS_BOOKED,
            AppointmentSlot::STATUS_BLOCKED,
        ]);

        $explicitKeys = $explicit
            ->mapWithKeys(fn (AppointmentSlot $s): array => [$s->starts_at->format('Y-m-d H:i') => true])
            ->all();

        $slots = [];

        // Créneaux virtuels issus de la règle par défaut.
        foreach ($this->availability->candidates() as $cand) {
            $start = $cand['start'];
            $end = $cand['end'];

            if (isset($explicitKeys[$start->format('Y-m-d H:i')])) {
                continue; // un créneau explicite couvre déjà ce moment
            }

            $overlapsOccupied = $occupied->contains(
                fn (AppointmentSlot $o): bool => $start < $o->ends_at && $end > $o->starts_at
            );
            if ($overlapsOccupied) {
                continue; // moment déjà pris / bloqué
            }

            $isBusy = $busy->contains(
                fn (AppointmentBusyBlock $b): bool => $start < $b->ends_at && $end > $b->starts_at
            );

            $slots[] = $this->serializeSlot(null, 'virtual', $start, $end, AppointmentSlot::STATUS_OPEN, $isBusy, $tz);
        }

        // Créneaux explicites.
        foreach ($explicit as $slot) {
            if (! $forAdmin && $slot->status !== AppointmentSlot::STATUS_OPEN) {
                continue; // le visiteur ne voit pas les créneaux réservés/bloqués
            }

            $start = $slot->starts_at->copy();
            $end = $slot->ends_at->copy();
            $isBusy = $busy->contains(
                fn (AppointmentBusyBlock $b): bool => $start < $b->ends_at && $end > $b->starts_at
            );

            $booking = ($forAdmin && $slot->booking) ? [
                'name' => $slot->booking->fullName(),
                'email' => $slot->booking->email,
                'phone' => $slot->booking->phone,
                'message' => $slot->booking->message,
            ] : null;

            $slots[] = $this->serializeSlot($slot->id, 'slot', $start, $end, $slot->status, $isBusy, $tz, $booking, $slot->notes);
        }

        usort($slots, fn (array $a, array $b): int => strcmp($a['startAt'], $b['startAt']));

        return [
            'slots' => $slots,
            'busy' => $this->mapBlocks($busy, $tz),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $booking
     * @return array<string, mixed>
     */
    private function serializeSlot(?int $id, string $kind, Carbon $start, Carbon $end, string $status, bool $busy, string $tz, ?array $booking = null, ?string $notes = null): array
    {
        $s = $start->copy()->timezone($tz);
        $e = $end->copy()->timezone($tz);

        return [
            'id' => $id,
            'kind' => $kind,
            'day' => $s->format('Y-m-d'),
            'start' => $s->format('H:i'),
            'end' => $e->format('H:i'),
            'startAt' => $s->format('Y-m-d H:i:00'),
            'endAt' => $e->format('Y-m-d H:i:00'),
            'busy' => $busy,
            'status' => $status,
            'notes' => $notes,
            'booking' => $booking,
        ];
    }

    /**
     * @param  Collection<int, AppointmentBusyBlock>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function mapBlocks(Collection $blocks, string $tz): array
    {
        return $blocks->map(function (AppointmentBusyBlock $block) use ($tz): array {
            $start = $block->starts_at->copy()->timezone($tz);
            $end = $block->ends_at->copy()->timezone($tz);

            return [
                'id' => $block->id,
                'day' => $start->format('Y-m-d'),
                'endDay' => $end->format('Y-m-d'),
                'start' => $start->format('H:i'),
                'end' => $end->format('H:i'),
                'title' => $block->title,
            ];
        })->values()->all();
    }
}
