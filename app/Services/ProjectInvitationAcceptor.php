<?php

namespace App\Services;

use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class ProjectInvitationAcceptor
{
    public function accept(ProjectInvitation $invitation, User $user): void
    {
        if ($invitation->accepted_at !== null) {
            return;
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'project_invitation' => 'Cette invitation a expiré. Demandez une nouvelle invitation à l’administrateur.',
            ]);
        }

        if (strcasecmp((string) $user->email, (string) $invitation->email) !== 0) {
            throw ValidationException::withMessages([
                'project_invitation' => 'Connectez-vous ou inscrivez-vous avec l’adresse e-mail qui a reçu l’invitation.',
            ]);
        }

        $project = $invitation->project;

        if (! $project->members()->where('users.id', $user->id)->exists()) {
            $project->members()->attach($user->id, [
                'can_view' => $invitation->can_view,
                'can_modify' => $invitation->can_modify,
                'can_annotate' => $invitation->can_annotate,
                'can_download' => $invitation->can_download,
            ]);
        }

        $invitation->update(['accepted_at' => now()]);
    }
}
