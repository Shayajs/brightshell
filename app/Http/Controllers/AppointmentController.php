<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentBookingRequest;
use App\Mail\AppointmentRequested;
use App\Models\AppointmentBooking;
use App\Models\AppointmentBusyBlock;
use App\Models\AppointmentSlot;
use App\Support\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentBookingRequest $request, AvailabilityService $availability): RedirectResponse
    {
        if ($request->isHoneypotTriggered()) {
            return redirect()
                ->route('agenda.index')
                ->with('success', 'Demande de rendez-vous envoyée. Je reviens vers vous très vite !');
        }

        $data = $request->validated();
        $start = Carbon::parse($data['starts_at']);
        $end = Carbon::parse($data['ends_at']);

        if ($start->isPast()) {
            return back()->withInput()->with('error', 'Ce créneau n’est plus disponible. Choisissez-en un autre.');
        }

        $booking = DB::transaction(function () use ($data, $request, $availability, $start, $end): ?AppointmentBooking {
            $slot = AppointmentSlot::query()
                ->where('starts_at', $start)
                ->where('ends_at', $end)
                ->lockForUpdate()
                ->first();

            // Créneau explicite déjà pris ou bloqué.
            if ($slot && $slot->status !== AppointmentSlot::STATUS_OPEN) {
                return null;
            }

            // Créneau virtuel : doit correspondre à la règle par défaut.
            if (! $slot && ! $availability->isScheduledSlot($start, $end)) {
                return null;
            }

            // Refus si chevauchement avec une indisponibilité.
            $busy = AppointmentBusyBlock::query()
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->exists();
            if ($busy) {
                return null;
            }

            // Refus si un autre créneau réservé/bloqué chevauche ce moment.
            $conflict = AppointmentSlot::query()
                ->whereIn('status', [AppointmentSlot::STATUS_BOOKED, AppointmentSlot::STATUS_BLOCKED])
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->exists();
            if ($conflict) {
                return null;
            }

            if (! $slot) {
                $slot = AppointmentSlot::create([
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'status' => AppointmentSlot::STATUS_OPEN,
                ]);
            }

            $booking = AppointmentBooking::create([
                'appointment_slot_id' => $slot->id,
                'status' => AppointmentBooking::STATUS_PENDING,
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'] ?? null,
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);

            $slot->update(['status' => AppointmentSlot::STATUS_BOOKED]);

            return $booking->load('slot');
        });

        if (! $booking) {
            return back()
                ->withInput()
                ->with('error', 'Ce créneau n’est plus disponible. Choisissez-en un autre.');
        }

        try {
            $recipient = (string) (config('brightshell.contact_recipient') ?: config('mail.from.address'));
            if ($recipient !== '') {
                Mail::to($recipient)->send(new AppointmentRequested($booking));
            }
        } catch (\Throwable $e) {
            Log::warning('appointment.mail_failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Demande de rendez-vous envoyée. Je vous confirme le créneau très vite !');
    }
}
