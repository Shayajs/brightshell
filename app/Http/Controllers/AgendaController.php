<?php

namespace App\Http\Controllers;

use App\Models\AppointmentBusyBlock;
use App\Models\AppointmentSlot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AgendaController extends Controller
{
    /**
     * Point d'entrée unique du sous-domaine agenda.* :
     * un admin connecté gère les créneaux, tout autre visiteur voit le
     * calendrier public de réservation.
     */
    public function index(Request $request): View
    {
        if ($this->isAdmin($request)) {
            return view('agenda.admin', $this->adminData());
        }

        return view('agenda.visitor', $this->visitorData(false));
    }

    /**
     * Aperçu de la version visiteur, réservé aux admins connectés (/visite).
     */
    public function preview(Request $request): View|RedirectResponse
    {
        if (! $this->isAdmin($request)) {
            return redirect()->route('agenda.index');
        }

        return view('agenda.visitor', $this->visitorData(true));
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && ($user->hasRole('admin') || $user->isAdmin());
    }

    /**
     * Données du calendrier visiteur : créneaux ouverts à venir (grisés si
     * chevauchés par une indisponibilité) + blocs occupés pour l'affichage.
     *
     * @return array<string, mixed>
     */
    private function visitorData(bool $preview): array
    {
        $tz = (string) config('app.timezone');

        $blocks = AppointmentBusyBlock::query()
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        $slots = AppointmentSlot::query()
            ->open()
            ->upcoming()
            ->orderBy('starts_at')
            ->get()
            ->map(fn (AppointmentSlot $slot): array => $this->mapSlot($slot, $blocks, $tz))
            ->values()
            ->all();

        return [
            'slots' => $slots,
            'busy' => $this->mapBlocks($blocks, $tz),
            'previewMode' => $preview,
        ];
    }

    /**
     * Données du calendrier admin : tous les créneaux récents/à venir (avec
     * réservation + drapeau d'occupation) et les blocs d'indisponibilité.
     *
     * @return array<string, mixed>
     */
    private function adminData(): array
    {
        $tz = (string) config('app.timezone');

        $blocks = AppointmentBusyBlock::query()
            ->where('ends_at', '>=', now()->subMonth())
            ->orderBy('starts_at')
            ->get();

        $slots = AppointmentSlot::query()
            ->with('booking')
            ->where('starts_at', '>=', now()->subMonth())
            ->orderBy('starts_at')
            ->get()
            ->map(function (AppointmentSlot $slot) use ($blocks, $tz): array {
                $data = $this->mapSlot($slot, $blocks, $tz);
                $data['status'] = $slot->status;
                $data['notes'] = $slot->notes;
                $data['booking'] = $slot->booking ? [
                    'name' => $slot->booking->fullName(),
                    'email' => $slot->booking->email,
                    'phone' => $slot->booking->phone,
                    'message' => $slot->booking->message,
                ] : null;

                return $data;
            })
            ->values()
            ->all();

        return [
            'slots' => $slots,
            'busy' => $this->mapBlocks($blocks, $tz),
        ];
    }

    /**
     * @param  Collection<int, AppointmentBusyBlock>  $blocks
     * @return array<string, mixed>
     */
    private function mapSlot(AppointmentSlot $slot, Collection $blocks, string $tz): array
    {
        $busy = $blocks->contains(
            fn (AppointmentBusyBlock $b): bool => $slot->starts_at < $b->ends_at && $slot->ends_at > $b->starts_at
        );

        $start = $slot->starts_at->copy()->timezone($tz);
        $end = $slot->ends_at->copy()->timezone($tz);

        return [
            'id' => $slot->id,
            'day' => $start->format('Y-m-d'),
            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),
            'busy' => $busy,
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
