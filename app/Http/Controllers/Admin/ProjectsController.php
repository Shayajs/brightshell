<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ProjectInvitationMail;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectsController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with('company')
            ->withCount('members')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('admin.projects.form', [
            'project' => null,
            'companies' => Company::query()->orderBy('name')->get(),
            'allUsers' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::create($this->validatedProjectPayload($request, null));

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('success', 'Projet créé. Ajoutez des membres et leurs droits.');
    }

    public function edit(Project $project): View
    {
        $project->load([
            'members' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
        ]);

        return view('admin.projects.form', [
            'project' => $project,
            'companies' => Company::query()->orderBy('name')->get(),
            'allUsers' => User::query()->orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validatedProjectPayload($request, $project));

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('success', 'Projet mis à jour.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        AdminAudit::record('project.deleted', $project, ['name' => $project->name]);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projet mis en corbeille.');
    }

    public function archive(Project $project): RedirectResponse
    {
        $project->update(['archived_at' => now()]);

        AdminAudit::record('project.archived', $project);

        return back()->with('success', 'Projet archivé (accès membres conservé).');
    }

    public function unarchive(Project $project): RedirectResponse
    {
        $project->update(['archived_at' => null]);

        AdminAudit::record('project.unarchived', $project);

        return back()->with('success', 'Projet sorti des archives.');
    }

    public function attachMember(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($project->members()->where('users.id', $data['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'Ce membre est déjà associé au projet.'])->withInput();
        }

        $project->members()->attach($data['user_id'], [
            'can_view' => $request->boolean('can_view', true),
            'can_modify' => $request->boolean('can_modify'),
            'can_annotate' => $request->boolean('can_annotate'),
            'can_download' => $request->boolean('can_download'),
        ]);

        $added = User::query()->find($data['user_id']);
        AdminAudit::record('project.member_attached', $project, [
            'user_id' => $data['user_id'],
            'email' => $added?->email,
        ]);

        return back()->with('success', 'Membre ajouté au projet.');
    }

    public function updateMember(Request $request, Project $project, User $user): RedirectResponse
    {
        if (! $project->members()->where('users.id', $user->id)->exists()) {
            abort(404);
        }

        $project->members()->updateExistingPivot($user->id, [
            'can_view' => $request->boolean('can_view'),
            'can_modify' => $request->boolean('can_modify'),
            'can_annotate' => $request->boolean('can_annotate'),
            'can_download' => $request->boolean('can_download'),
        ]);

        AdminAudit::record('project.member_rights_updated', $project, [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->with('success', 'Droits mis à jour.');
    }

    public function detachMember(Project $project, User $user): RedirectResponse
    {
        $project->members()->detach($user->id);

        AdminAudit::record('project.member_detached', $project, [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->with('success', 'Membre retiré du projet.');
    }

    public function inviteByEmail(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'invite_email' => ['required', 'email'],
            'invite_can_view' => ['nullable'],
            'invite_can_modify' => ['nullable'],
            'invite_can_annotate' => ['nullable'],
            'invite_can_download' => ['nullable'],
        ]);

        $email = strtolower(trim((string) $request->input('invite_email')));

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return back()
                ->withErrors(['invite_email' => 'Ce compte existe déjà : ajoutez-le via la liste des membres.'])
                ->withInput();
        }

        if (ProjectInvitation::query()
            ->where('project_id', $project->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->exists()) {
            return back()
                ->withErrors(['invite_email' => 'Une invitation est déjà en cours pour cette adresse.'])
                ->withInput();
        }

        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'email' => $email,
            'invited_by_user_id' => $request->user()?->id,
            'can_view' => $request->boolean('invite_can_view', true),
            'can_modify' => $request->boolean('invite_can_modify'),
            'can_annotate' => $request->boolean('invite_can_annotate'),
            'can_download' => $request->boolean('invite_can_download'),
            'expires_at' => now()->addDays(14),
        ]);

        Mail::to($email)->send(new ProjectInvitationMail($invitation));

        AdminAudit::record('project.invitation_sent', $project, ['email' => $email]);

        return back()->with('success', 'Invitation envoyée à '.$email.'.');
    }

    /** @return array<string, mixed> */
    private function validatedProjectPayload(Request $request, ?Project $project): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('projects', 'slug')->ignore($project?->id)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ]);

        if (array_key_exists('slug', $data) && ($data['slug'] === null || $data['slug'] === '')) {
            unset($data['slug']);
        }

        return $data;
    }
}
