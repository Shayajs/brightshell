<?php

namespace App\Mail;

use App\Models\AppointmentBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AppointmentBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        $slot = $this->booking->slot;
        $when = $slot?->formattedRange() ?? 'créneau inconnu';

        return new Envelope(
            subject: '[RDV] '.$when.' — '.$this->booking->fullName(),
            replyTo: [
                new Address($this->booking->email, $this->booking->fullName()),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-requested',
            text: 'emails.appointment-requested-plain',
            with: [
                'booking' => $this->booking,
                'slot' => $this->booking->slot,
            ],
        );
    }
}
