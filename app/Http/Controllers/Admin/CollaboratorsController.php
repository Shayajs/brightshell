<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorTeam;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollaboratorsController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'active');
        $teamId = $request->query('team');
        $q = trim((string) $request->query('q', ''));

        $query = User::query()
            ->collaboratorPortalUsers()
            ->with(['roles', 'collaboratorTeams'])
            ->orderByDesc('id');

        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function (Builder $sub) use ($like): void {
                $sub->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhereRaw("concat(coalesce(first_name,''), ' ', coalesce(last_name,'')) like ?", [$like]);
            });
        }

        if ($teamId !== null && $teamId !== '') {
            $tid = (int) $teamId;
            if ($tid > 0) {
                $query->whereHas('collaboratorTeams', fn (Builder $t) => $t->where('collaborator_teams.id', $tid));
            }
        }

        $collaborators = $query->paginate(25)->withQueryString();
        $teams = CollaboratorTeam::query()->orderBy('name')->get();

        return view('admin.collaborators.index', compact('collaborators', 'status', 'teams', 'teamId', 'q'));
    }
}
