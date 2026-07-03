<?php

namespace App\Http\Controllers;

use App\Models\AppointmentSlot;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    /**
     * Point d'entrée unique du sous-domaine agenda.* :
     * un admin connecté gère les créneaux, tout autre visiteur voit la
     * page publique de réservation.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user !== null && ($user->hasRole('admin') || $user->isAdmin())) {
            $slots = AppointmentSlot::query()
                ->with('booking')
                ->where('starts_at', '>=', now()->subDays(7))
                ->orderBy('starts_at')
                ->paginate(30);

            return view('admin.agenda.index', compact('slots'));
        }

        $slots = AppointmentSlot::query()
            ->open()
            ->upcoming()
            ->orderBy('starts_at')
            ->get();

        return view('appointments.index', compact('slots'));
    }
}
