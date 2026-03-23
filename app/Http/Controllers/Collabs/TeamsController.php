<?php

namespace App\Http\Controllers\Collabs;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorTeam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CollaboratorTeam::class);

        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        if ($user->isAdmin()) {
            $teams = CollaboratorTeam::query()->orderBy('name')->get();
        } else {
            $teams = $user->collaboratorTeams()->orderBy('name')->get();
        }

        return view('portals.collabs.teams.index', compact('teams'));
    }

    public function show(Request $request, CollaboratorTeam $collab_team): View
    {
        $this->authorize('view', $collab_team);

        $collab_team->load(['capabilities', 'users' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name')]);

        return view('portals.collabs.teams.show', [
            'team' => $collab_team,
        ]);
    }
}
