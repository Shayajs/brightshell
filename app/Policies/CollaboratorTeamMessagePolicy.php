<?php

namespace App\Policies;

use App\Models\CollaboratorTeam;
use App\Models\CollaboratorTeamMessage;
use App\Models\User;

class CollaboratorTeamMessagePolicy
{
    public function viewAny(User $user, CollaboratorTeam $team): bool
    {
        return $user->isCollaboratorPortalUser()
            && ($user->isAdmin() || $team->hasMember($user));
    }

    public function create(User $user, CollaboratorTeam $team): bool
    {
        return $this->viewAny($user, $team);
    }

    public function view(User $user, CollaboratorTeamMessage $message): bool
    {
        $team = $message->team;

        return $this->viewAny($user, $team);
    }
}
