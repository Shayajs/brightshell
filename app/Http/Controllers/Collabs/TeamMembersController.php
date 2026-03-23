<?php

namespace App\Http\Controllers\Collabs;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorTeam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamMembersController extends Controller
{
    public function store(Request $request, CollaboratorTeam $collab_team): RedirectResponse
    {
        $this->authorize('addMember', $collab_team);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $target = User::query()->where('email', $request->string('email')->trim()->lower())->first();

        if ($target === null) {
            return back()->withErrors(['email' => 'Aucun compte avec cet e-mail.'])->withInput();
        }

        if (! $target->isCollaboratorPortalUser()) {
            return back()->withErrors(['email' => 'Ce compte n’est pas collaborateur (rôle ou admin requis).'])->withInput();
        }

        if ($collab_team->hasMember($target)) {
            return back()->with('error', 'Cette personne est déjà dans l’équipe.');
        }

        $collab_team->users()->attach($target->id, ['is_team_manager' => false]);

        return back()->with('success', 'Membre ajouté à l’équipe.');
    }

    public function destroy(Request $request, CollaboratorTeam $collab_team, User $user): RedirectResponse
    {
        $this->authorize('removeMember', [$collab_team, $user]);

        if (! $collab_team->hasMember($user)) {
            return back()->with('error', 'Ce membre n’appartient pas à cette équipe.');
        }

        $collab_team->users()->detach($user->id);

        return back()->with('success', 'Membre retiré de l’équipe.');
    }

    public function updateManager(Request $request, CollaboratorTeam $collab_team, User $user): RedirectResponse
    {
        $this->authorize('setTeamManagerStatus', [$collab_team, $user]);

        $request->validate([
            'is_team_manager' => ['required', 'boolean'],
        ]);

        if (! $collab_team->hasMember($user)) {
            return back()->with('error', 'Ce membre n’appartient pas à cette équipe.');
        }

        $collab_team->users()->updateExistingPivot($user->id, [
            'is_team_manager' => $request->boolean('is_team_manager'),
        ]);

        return back()->with('success', 'Statut gérant mis à jour.');
    }
}
