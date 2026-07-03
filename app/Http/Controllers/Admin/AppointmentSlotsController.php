<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSlot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentSlotsController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:single,bulk'],
            'starts_at' => ['required_if:mode,single', 'nullable', 'date'],
            'ends_at' => ['required_if:mode,single', 'nullable', 'date', 'after:starts_at'],
            'bulk_date' => ['required_if:mode,bulk', 'nullable', 'date', 'after_or_equal:today'],
            'bulk_from' => ['required_if:mode,bulk', 'nullable', 'date_format:H:i'],
            'bulk_to' => ['required_if:mode,bulk', 'nullable', 'date_format:H:i', 'after:bulk_from'],
            'bulk_duration' => ['required_if:mode,bulk', 'nullable', 'integer', 'in:15,30,45,60'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $created = 0;

        if ($data['mode'] === 'single') {
            AppointmentSlot::create([
                'starts_at' => Carbon::parse($data['starts_at']),
                'ends_at' => Carbon::parse($data['ends_at']),
                'status' => AppointmentSlot::STATUS_OPEN,
                'notes' => $data['notes'] ?? null,
            ]);
            $created = 1;
        } else {
            $date = Carbon::parse($data['bulk_date'])->startOfDay();
            $cursor = $date->copy()->setTimeFromTimeString($data['bulk_from']);
            $endBoundary = $date->copy()->setTimeFromTimeString($data['bulk_to']);
            $duration = (int) $data['bulk_duration'];

            while ($cursor->copy()->addMinutes($duration)->lte($endBoundary)) {
                $slotEnd = $cursor->copy()->addMinutes($duration);

                if ($slotEnd->isFuture()) {
                    AppointmentSlot::create([
                        'starts_at' => $cursor->copy(),
                        'ends_at' => $slotEnd,
                        'status' => AppointmentSlot::STATUS_OPEN,
                        'notes' => $data['notes'] ?? null,
                    ]);
                    $created++;
                }

                $cursor->addMinutes($duration);
            }
        }

        if ($created === 0) {
            return back()->with('error', 'Aucun créneau créé (vérifiez les horaires et la date).');
        }

        return back()->with('success', $created === 1 ? 'Créneau ajouté.' : "{$created} créneaux ajoutés.");
    }

    public function update(Request $request, AppointmentSlot $slot): RedirectResponse
    {
        if ($slot->status === AppointmentSlot::STATUS_BOOKED) {
            return back()->with('error', 'Impossible de modifier un créneau déjà réservé.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:'.AppointmentSlot::STATUS_OPEN.','.AppointmentSlot::STATUS_BLOCKED],
        ]);

        $slot->update(['status' => $data['status']]);

        return back()->with('success', 'Créneau mis à jour.');
    }

    public function destroy(AppointmentSlot $slot): RedirectResponse
    {
        if ($slot->status === AppointmentSlot::STATUS_BOOKED) {
            return back()->with('error', 'Impossible de supprimer un créneau réservé.');
        }

        $slot->delete();

        return back()->with('success', 'Créneau supprimé.');
    }
}
