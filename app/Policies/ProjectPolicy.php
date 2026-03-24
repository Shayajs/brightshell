<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return true;
        }

        return Project::query()->accessibleByNonAdmin($user)->exists();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return true;
        }

        $pivot = $project->membershipFor($user);

        return $pivot !== null && (bool) $pivot->can_view;
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return true;
        }

        $pivot = $project->membershipFor($user);

        return $pivot !== null && (bool) $pivot->can_modify;
    }

    public function annotate(User $user, Project $project): bool
    {
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return true;
        }

        $pivot = $project->membershipFor($user);

        return $pivot !== null && (bool) $pivot->can_annotate;
    }

    public function download(User $user, Project $project): bool
    {
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return true;
        }

        $pivot = $project->membershipFor($user);

        return $pivot !== null && (bool) $pivot->can_download;
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }
}
