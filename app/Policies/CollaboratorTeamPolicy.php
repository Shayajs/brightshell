<?php

namespace App\Policies;

use App\Models\CollaboratorTeam;
use App\Models\User;

class CollaboratorTeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCollaboratorPortalUser();
    }

    public function view(User $user, CollaboratorTeam $team): bool
    {
        if (! $user->isCollaboratorPortalUser()) {
            return false;
        }

        return $user->isAdmin() || $team->hasMember($user);
    }

    public function addMember(User $user, CollaboratorTeam $team): bool
    {
        if (! $user->isCollaboratorPortalUser()) {
            return false;
        }

        return $user->isAdmin() || $user->managesCollaboratorTeam($team);
    }

    public function removeMember(User $user, CollaboratorTeam $team, User $target): bool
    {
        if (! $user->isCollaboratorPortalUser()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($team->memberIsTeamManager($target)) {
            return $user->canAssignCollaboratorTeamManagers();
        }

        return $user->managesCollaboratorTeam($team);
    }

    public function setTeamManagerStatus(User $user, CollaboratorTeam $team, User $target): bool
    {
        if (! $user->isCollaboratorPortalUser()) {
            return false;
        }

        if (! $user->canAssignCollaboratorTeamManagers()) {
            return false;
        }

        return $team->hasMember($target);
    }

    /**
     * Modifier les droits (capabilities) d’une équipe.
     */
    public function updateCapabilities(User $user, CollaboratorTeam $team): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isCollaboratorPortalUser() || ! $user->belongsToAdminCollaboratorTeam()) {
            return false;
        }

        if ($team->is_admin_team) {
            return false;
        }

        return true;
    }

    public function participateInMessages(User $user, CollaboratorTeam $team): bool
    {
        return $this->view($user, $team);
    }
}
