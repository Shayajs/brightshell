<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentBookingRequest;
use App\Mail\AppointmentRequested;
use App\Models\AppointmentBooking;
use App\Models\AppointmentSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function create(): View
    {
        $slots = AppointmentSlot::query()
            ->open()
            ->upcoming()
            ->orderBy('starts_at')
            ->get();

        return view('appointments.index', compact('slots'));
    }

    public function store(StoreAppointmentBookingRequest $request): RedirectResponse
    {
        if ($request->isHoneypotTriggered()) {
            return redirect()
                ->route('appointments')
                ->with('success', 'Demande de rendez-vous envoyée. Je reviens vers vous très vite !');
        }

        $data = $request->validated();

        $booking = DB::transaction(function () use ($data, $request): ?AppointmentBooking {
            $slot = AppointmentSlot::query()
                ->whereKey($data['appointment_slot_id'])
                ->lockForUpdate()
                ->first();

            if (! $slot || $slot->status !== AppointmentSlot::STATUS_OPEN || $slot->starts_at->isPast()) {
                return null;
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
            ->route('appointments')
            ->with('success', 'Demande de rendez-vous envoyée. Je vous confirme le créneau très vite !');
    }
}
