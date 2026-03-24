<?php

namespace App\Mail;

use App\Models\ProjectInvitation;
use App\Support\BrightshellAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProjectInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        $project = $this->invitation->project;

        return new Envelope(
            subject: 'Invitation au projet « '.$project->name.' » — BrightShell',
        );
    }

    public function content(): Content
    {
        $base = BrightshellAccount::portalBaseUrl();
        $acceptUrl = $base.'/project-invitation/'.$this->invitation->token;
        $registerUrl = $base.'/register?project_invitation='.$this->invitation->token;

        return new Content(
            html: 'emails.project-invitation',
            text: 'emails.project-invitation-plain',
            with: [
                'projectName' => $this->invitation->project->name,
                'acceptUrl' => $acceptUrl,
                'registerUrl' => $registerUrl,
                'invitedEmail' => $this->invitation->email,
            ],
        );
    }
}
