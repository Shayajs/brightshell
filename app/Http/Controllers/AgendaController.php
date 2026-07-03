<?php

namespace App\Http\Controllers;

use App\Models\AppointmentSlot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            return view('agenda.admin', [
                'slots' => $this->adminSlots(),
            ]);
        }

        return view('agenda.visitor', [
            'slots' => $this->visitorSlots(),
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

        return view('agenda.visitor', [
            'slots' => $this->visitorSlots(),
            'previewMode' => true,
        ]);
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && ($user->hasRole('admin') || $user->isAdmin());
    }

    /**
     * Créneaux disponibles (visiteur) : ouverts et à venir.
     *
     * @return array<int, array<string, mixed>>
     */
    private function visitorSlots(): array
    {
        $tz = (string) config('app.timezone');

        return AppointmentSlot::query()
            ->open()
            ->upcoming()
            ->orderBy('starts_at')
            ->get()
            ->map(function (AppointmentSlot $slot) use ($tz): array {
                $start = $slot->starts_at->copy()->timezone($tz);
                $end = $slot->ends_at->copy()->timezone($tz);

                return [
                    'id' => $slot->id,
                    'day' => $start->format('Y-m-d'),
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Créneaux pour la gestion admin (tous les récents + à venir, avec réservation).
     *
     * @return array<int, array<string, mixed>>
     */
    private function adminSlots(): array
    {
        $tz = (string) config('app.timezone');

        return AppointmentSlot::query()
            ->with('booking')
            ->where('starts_at', '>=', now()->subMonth())
            ->orderBy('starts_at')
            ->get()
            ->map(function (AppointmentSlot $slot) use ($tz): array {
                $start = $slot->starts_at->copy()->timezone($tz);
                $end = $slot->ends_at->copy()->timezone($tz);

                return [
                    'id' => $slot->id,
                    'day' => $start->format('Y-m-d'),
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i'),
                    'status' => $slot->status,
                    'notes' => $slot->notes,
                    'booking' => $slot->booking ? [
                        'name' => $slot->booking->fullName(),
                        'email' => $slot->booking->email,
                        'phone' => $slot->booking->phone,
                        'message' => $slot->booking->message,
                    ] : null,
                ];
            })
            ->values()
            ->all();
    }
}
