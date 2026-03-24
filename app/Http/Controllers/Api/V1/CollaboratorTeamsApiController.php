<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorCapability;
use App\Models\CollaboratorTeam;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollaboratorTeamsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CollaboratorTeam::class);

        $user = $request->user();
        $teams = $user->isAdmin()
            ? CollaboratorTeam::query()->orderBy('name')->get()
            : $user->collaboratorTeams()->orderBy('name')->get();

        return response()->json([
            'data' => $teams->map(fn (CollaboratorTeam $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'is_admin_team' => (bool) $t->is_admin_team,
            ]),
        ]);
    }

    public function show(Request $request, CollaboratorTeam $collab_team): JsonResponse
    {
        $this->authorize('view', $collab_team);

        $collab_team->load([
            'capabilities',
            'users' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
        ]);

        return response()->json([
            'data' => [
                'id' => $collab_team->id,
                'name' => $collab_team->name,
                'is_admin_team' => (bool) $collab_team->is_admin_team,
                'capabilities' => $collab_team->capabilities,
                'users' => $collab_team->users->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'pivot' => [
                        'is_team_manager' => (bool) ($u->pivot->is_team_manager ?? false),
                    ],
                ]),
            ],
        ]);
    }

    public function messagesPoll(Request $request, CollaboratorTeam $collab_team): JsonResponse
    {
        $this->authorize('participateInMessages', $collab_team);

        $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $afterId = (int) $request->input('after_id', 0);

        $rows = $collab_team->messages()
            ->with('user')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(200)
            ->get();

        return response()->json([
            'messages' => $rows->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'created_at' => $m->created_at?->toIso8601String(),
                'user' => $m->user ? [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                ] : null,
            ]),
        ]);
    }

    public function messagesStore(Request $request, CollaboratorTeam $collab_team): JsonResponse
    {
        $this->authorize('participateInMessages', $collab_team);

        $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $message = $collab_team->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->trim(),
        ]);

        $message->load('user');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
            ],
        ], 201);
    }

    public function capabilitiesCatalog(Request $request): JsonResponse
    {
        abort_unless($request->user()->isCollaboratorPortalUser() || $request->user()->isAdmin(), 403);

        $all = CollaboratorCapability::query()->orderBy('label')->get();

        return response()->json([
            'data' => $all->map(fn (CollaboratorCapability $c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'label' => $c->label,
            ]),
        ]);
    }

    public function updateCapabilities(Request $request, CollaboratorTeam $collab_team): JsonResponse
    {
        $this->authorize('updateCapabilities', $collab_team);

        $request->validate([
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['integer', 'exists:collaborator_capabilities,id'],
        ]);

        $ids = array_map('intval', $request->input('capabilities', []));
        $collab_team->capabilities()->sync($ids);

        return response()->json(['message' => 'Permissions mises à jour.']);
    }

    public function storeMember(Request $request, CollaboratorTeam $collab_team): JsonResponse
    {
        $this->authorize('addMember', $collab_team);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $target = User::query()->where('email', $request->string('email')->trim()->lower())->first();

        if ($target === null) {
            return response()->json(['message' => 'Aucun compte avec cet e-mail.'], 422);
        }

        if (! $target->isCollaboratorPortalUser()) {
            return response()->json(['message' => 'Ce compte n’est pas collaborateur.'], 422);
        }

        if ($collab_team->hasMember($target)) {
            return response()->json(['message' => 'Déjà membre de l’équipe.'], 422);
        }

        $collab_team->users()->attach($target->id, ['is_team_manager' => false]);

        return response()->json(['message' => 'Membre ajouté.', 'data' => ['user_id' => $target->id]], 201);
    }

    public function destroyMember(Request $request, CollaboratorTeam $collab_team, User $user): JsonResponse
    {
        $this->authorize('removeMember', [$collab_team, $user]);

        if (! $collab_team->hasMember($user)) {
            return response()->json(['message' => 'Ce membre n’appartient pas à cette équipe.'], 422);
        }

        $collab_team->users()->detach($user->id);

        return response()->json(['message' => 'Membre retiré.']);
    }

    public function updateMemberManager(Request $request, CollaboratorTeam $collab_team, User $user): JsonResponse
    {
        $this->authorize('setTeamManagerStatus', [$collab_team, $user]);

        $request->validate([
            'is_team_manager' => ['required', 'boolean'],
        ]);

        if (! $collab_team->hasMember($user)) {
            return response()->json(['message' => 'Ce membre n’appartient pas à cette équipe.'], 422);
        }

        $collab_team->users()->updateExistingPivot($user->id, [
            'is_team_manager' => $request->boolean('is_team_manager'),
        ]);

        return response()->json(['message' => 'Statut gérant mis à jour.']);
    }
}
