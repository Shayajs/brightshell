<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorCapability;
use App\Models\CollaboratorTeam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CollaboratorTeamsController extends Controller
{
    public function index(): View
    {
        $teams = CollaboratorTeam::query()
            ->withCount(['users', 'capabilities'])
            ->orderBy('name')
            ->get();

        return view('admin.collaborator-teams.index', compact('teams'));
    }

    public function create(): View
    {
        $allCapabilities = CollaboratorCapability::query()->orderBy('label')->get();

        return view('admin.collaborator-teams.create', compact('allCapabilities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedTeamPayload($request);

        $team = DB::transaction(function () use ($data): CollaboratorTeam {
            $team = CollaboratorTeam::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'is_admin_team' => $data['is_admin_team'],
            ]);

            if ($data['is_admin_team']) {
                CollaboratorTeam::query()->where('id', '!=', $team->id)->update(['is_admin_team' => false]);
            }

            $team->capabilities()->sync($data['capability_ids']);

            return $team;
        });

        $this->assertAtLeastOneAdminTeam();

        return redirect()
            ->route('admin.collaborator-teams.edit', $team)
            ->with('success', 'Groupe créé.');
    }

    public function edit(CollaboratorTeam $collaborator_team): View
    {
        $collaborator_team->load([
            'capabilities',
            'users' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
        ]);
        $allCapabilities = CollaboratorCapability::query()->orderBy('label')->get();

        return view('admin.collaborator-teams.edit', [
            'team' => $collaborator_team,
            'allCapabilities' => $allCapabilities,
        ]);
    }

    public function update(Request $request, CollaboratorTeam $collaborator_team): RedirectResponse
    {
        $data = $this->validatedTeamPayload($request, $collaborator_team);

        DB::transaction(function () use ($collaborator_team, $data): void {
            if ($data['is_admin_team']) {
                CollaboratorTeam::query()->where('id', '!=', $collaborator_team->id)->update(['is_admin_team' => false]);
            } elseif ($collaborator_team->is_admin_team) {
                $otherAdmins = CollaboratorTeam::query()
                    ->where('is_admin_team', true)
                    ->where('id', '!=', $collaborator_team->id)
                    ->exists();
                if (! $otherAdmins) {
                    throw ValidationException::withMessages([
                        'is_admin_team' => 'Il doit rester au moins une équipe d’administration. Nommez d’abord une autre équipe comme telle, ou conservez ce statut.',
                    ]);
                }
            }

            $collaborator_team->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'is_admin_team' => $data['is_admin_team'],
            ]);

            $collaborator_team->capabilities()->sync($data['capability_ids']);
        });

        $this->assertAtLeastOneAdminTeam();

        return back()->with('success', 'Groupe mis à jour.');
    }

    public function destroy(CollaboratorTeam $collaborator_team): RedirectResponse
    {
        if ($collaborator_team->is_admin_team) {
            return back()->with('error', 'Impossible de supprimer l’équipe d’administration collaborateurs.');
        }

        $collaborator_team->delete();

        return redirect()
            ->route('admin.collaborator-teams.index')
            ->with('success', 'Groupe supprimé. Les membres ne sont plus rattachés à ce groupe (les autres groupes et rôles restent inchangés).');
    }

    public function storeMember(Request $request, CollaboratorTeam $collaborator_team): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $target = User::query()->where('email', $request->string('email')->trim()->lower())->first();

        if ($target === null) {
            return back()->withErrors(['email' => 'Aucun compte avec cet e-mail.'])->withInput();
        }

        if (! $target->isCollaboratorPortalUser()) {
            return back()->withErrors(['email' => 'Ce compte n’est pas collaborateur (rôle admin / collaborator ou admin plateforme requis).'])->withInput();
        }

        if ($collaborator_team->hasMember($target)) {
            return back()->with('error', 'Cette personne est déjà dans ce groupe.');
        }

        $collaborator_team->users()->attach($target->id, ['is_team_manager' => false]);

        return back()->with('success', 'Membre ajouté au groupe.');
    }

    public function destroyMember(CollaboratorTeam $collaborator_team, User $user): RedirectResponse
    {
        if (! $collaborator_team->hasMember($user)) {
            return back()->with('error', 'Ce membre n’appartient pas à ce groupe.');
        }

        $collaborator_team->users()->detach($user->id);

        return back()->with('success', 'Membre retiré du groupe.');
    }

    public function updateMemberManager(Request $request, CollaboratorTeam $collaborator_team, User $user): RedirectResponse
    {
        $request->validate([
            'is_team_manager' => ['required', 'boolean'],
        ]);

        if (! $collaborator_team->hasMember($user)) {
            return back()->with('error', 'Ce membre n’appartient pas à ce groupe.');
        }

        $collaborator_team->users()->updateExistingPivot($user->id, [
            'is_team_manager' => $request->boolean('is_team_manager'),
        ]);

        return back()->with('success', 'Statut gérant mis à jour.');
    }

    /**
     * @return array{name: string, slug: string, is_admin_team: bool, capability_ids: list<int>}
     */
    protected function validatedTeamPayload(Request $request, ?CollaboratorTeam $team = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'slug' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['integer', 'exists:collaborator_capabilities,id'],
        ]);

        $name = $validated['name'];
        $slugInput = isset($validated['slug']) && $validated['slug'] !== '' ? $validated['slug'] : null;
        $slug = $slugInput ?? ($team !== null ? $team->slug : Str::slug($name));

        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Le nom ne permet pas de générer un identifiant (slug) valide. Précisez un slug manuellement.',
            ]);
        }

        $slugTaken = CollaboratorTeam::query()
            ->where('slug', $slug)
            ->when($team !== null, fn ($q) => $q->where('id', '!=', $team->id))
            ->exists();

        if ($slugTaken) {
            throw ValidationException::withMessages([
                'slug' => 'Ce slug est déjà utilisé. Choisissez un autre identifiant.',
            ]);
        }

        $ids = array_values(array_unique(array_map('intval', $validated['capabilities'] ?? [])));

        return [
            'name' => $name,
            'slug' => $slug,
            'is_admin_team' => $request->boolean('is_admin_team'),
            'capability_ids' => $ids,
        ];
    }

    protected function assertAtLeastOneAdminTeam(): void
    {
        if (CollaboratorTeam::query()->where('is_admin_team', true)->count() < 1) {
            throw ValidationException::withMessages([
                'is_admin_team' => 'Il doit exister au moins une équipe d’administration collaborateurs.',
            ]);
        }
    }
}
