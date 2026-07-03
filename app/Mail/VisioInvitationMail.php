<?php

namespace App\Mail;

use App\Models\VisioInvitation;
use App\Support\PortalUrls;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisioInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public VisioInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation visioconférence « '.$this->invitation->room->title.' » — BrightShell',
        );
    }

    public function content(): Content
    {
        $base = rtrim(PortalUrls::visioUrl(), '/');
        $joinUrl = $base.'/join/'.$this->invitation->token;

        return new Content(
            html: 'emails.visio-invitation',
            text: 'emails.visio-invitation-plain',
            with: [
                'roomTitle' => $this->invitation->room->title,
                'projectName' => $this->invitation->room->project?->name,
                'joinUrl' => $joinUrl,
                'invitedEmail' => $this->invitation->email,
            ],
        );
    }
}
