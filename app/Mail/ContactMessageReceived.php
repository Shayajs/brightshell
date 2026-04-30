<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $message
    ) {}

    public function envelope(): Envelope
    {
        $typeLabel = ContactMessage::typeLabel($this->message->type);
        $headline = $this->message->subject
            ?? $this->message->project_title
            ?? $this->message->fullName();

        $subject = '[Contact – '.$typeLabel.'] '.Str::limit($headline, 80);

        return new Envelope(
            subject: $subject,
            replyTo: [
                new Address($this->message->email, $this->message->fullName()),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
            text: 'emails.contact-message-plain',
            with: [
                'message' => $this->message,
                'typeLabel' => ContactMessage::typeLabel($this->message->type),
            ],
        );
    }
}
