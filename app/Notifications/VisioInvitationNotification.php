<?php

namespace App\Notifications;

use App\Models\VisioInvitation;
use App\Support\PortalUrls;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VisioInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly VisioInvitation $invitation
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $base = rtrim(PortalUrls::visioUrl(), '/');

        return [
            'title' => 'Invitation visio',
            'body' => 'Nouvelle invitation: '.$this->invitation->room->title,
            'join_url' => $base.'/join/'.$this->invitation->token,
            'room_slug' => $this->invitation->room->slug,
            'project_name' => $this->invitation->room->project?->name,
        ];
    }
}
