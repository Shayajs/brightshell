<?php

namespace App\Http\Controllers\Collabs;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorCapability;
use App\Models\CollaboratorTeam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamPermissionsController extends Controller
{
    public function edit(Request $request, CollaboratorTeam $collab_team): View
    {
        $this->authorize('view', $collab_team);

        $collab_team->load('capabilities');
        $allCapabilities = CollaboratorCapability::query()->orderBy('label')->get();
        $canEdit = $request->user()?->can('updateCapabilities', $collab_team) ?? false;

        return view('portals.collabs.teams.permissions', [
            'team' => $collab_team,
            'allCapabilities' => $allCapabilities,
            'canEdit' => $canEdit,
        ]);
    }

    public function update(Request $request, CollaboratorTeam $collab_team): RedirectResponse
    {
        $this->authorize('updateCapabilities', $collab_team);

        $request->validate([
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['integer', 'exists:collaborator_capabilities,id'],
        ]);

        $ids = array_map('intval', $request->input('capabilities', []));
        $collab_team->capabilities()->sync($ids);

        return back()->with('success', 'Permissions de l’équipe mises à jour.');
    }
}
