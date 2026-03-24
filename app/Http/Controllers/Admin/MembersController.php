<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorTeam;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminAudit;
use App\Support\AdminEmailVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembersController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'active');
        $query = User::with('roles')->orderByDesc('id');

        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

        $members = $query->paginate(25)->withQueryString();

        return view('admin.members.index', compact('members', 'status'));
    }

    public function create(): View
    {
        $allRoles = Role::orderBy('priority', 'desc')->get();

        return view('admin.members.create', compact('allRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'members' => ['required', 'array', 'min:1'],
            'members.*.first_name' => ['required', 'string', 'max:255'],
            'members.*.last_name' => ['required', 'string', 'max:255'],
            'members.*.email' => ['required', 'email', 'max:255', 'distinct'],
            'members.*.password' => ['nullable', 'string', 'min:8'],
            'members.*.password_confirmation' => ['nullable', 'string'],
            'members.*.roles' => ['nullable', 'array'],
            'members.*.roles.*' => ['integer', 'exists:roles,id'],
            'members.*.is_admin' => ['nullable'],
        ]);

        // Unicité des e-mails en base
        foreach ($request->input('members') as $i => $m) {
            if (User::where('email', $m['email'])->exists()) {
                return back()
                    ->withErrors(["members.{$i}.email" => "L'adresse {$m['email']} est déjà utilisée."])
                    ->withInput();
            }
        }

        $created = [];
        $generatedPasswords = [];

        foreach ($request->input('members') as $m) {
            $raw = $m['password'] ?? null;
            $generated = false;

            if (empty($raw)) {
                $raw = Str::random(16);
                $generated = true;
            }

            $member = User::create([
                'first_name' => $m['first_name'],
                'last_name' => $m['last_name'],
                'email' => $m['email'],
                'password' => Hash::make($raw),
                'is_admin' => ! empty($m['is_admin']),
                'email_verified_at' => now(),
            ]);

            if (! empty($m['roles'])) {
                $member->roles()->sync($m['roles']);
            }

            AdminAudit::record('member.created', $member, [
                'email' => $member->email,
                'is_admin' => $member->is_admin,
            ]);

            $created[] = $member;

            if ($generated) {
                $generatedPasswords[$member->email] = $raw;
            }
        }

        $names = implode(', ', array_map(fn ($u) => $u->name, $created));

        if (count($created) === 1) {
            return redirect()
                ->route('admin.members.show', $created[0])
                ->with('success', "Membre {$names} créé avec succès.")
                ->with('generated_passwords', $generatedPasswords ?: null);
        }

        return redirect()
            ->route('admin.members.index')
            ->with('success', count($created)." membres créés : {$names}.")
            ->with('generated_passwords', $generatedPasswords ?: null);
    }

    public function verifyEmail(User $member): RedirectResponse
    {
        if ($member->trashed()) {
            return back()->with('error', 'Ce compte est archivé.');
        }

        if ($member->hasVerifiedEmail()) {
            return back()->with('success', 'Cette adresse e-mail est déjà confirmée.');
        }

        $member->markEmailAsVerified();
        event(new Verified($member));

        return back()->with('success', 'Adresse e-mail confirmée manuellement.');
    }

    public function show(User $member): View
    {
        $member->load('roles', 'companies', 'collaboratorTeams');
        $allRoles = Role::orderBy('priority', 'desc')->get();
        $collaboratorTeams = CollaboratorTeam::query()->orderBy('name')->get();

        return view('admin.members.show', compact('member', 'allRoles', 'collaboratorTeams'));
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        if ($member->trashed()) {
            return back()->with('error', 'Les informations d’un compte archivé ne peuvent pas être modifiées.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'profile_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $before = [
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => $member->email,
            'phone' => $member->phone,
            'profile_notes' => $member->profile_notes,
        ];

        $member->update($validated);

        AdminAudit::record('member.profile_updated', $member, [
            'before' => $before,
            'after' => [
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'profile_notes' => $member->profile_notes,
            ],
        ]);

        return back()->with('success', 'Informations internes mises à jour.');
    }

    public function updateRoles(Request $request, User $member): RedirectResponse
    {
        if ($member->trashed()) {
            return back()->with('error', 'Les rôles d’un compte archivé ne peuvent pas être modifiés.');
        }

        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $beforeRoles = $member->roles()->pluck('slug')->sort()->values()->all();
        $beforeAdmin = $member->is_admin;

        $member->roles()->sync($request->input('roles', []));

        if ($request->has('is_admin')) {
            $member->update(['is_admin' => (bool) $request->input('is_admin')]);
        }

        $member->refresh();
        AdminEmailVerification::ensureVerifiedIfAdmin($member);

        AdminAudit::record('member.roles_updated', $member, [
            'roles_before' => $beforeRoles,
            'roles_after' => $member->roles()->pluck('slug')->sort()->values()->all(),
            'is_admin_before' => $beforeAdmin,
            'is_admin_after' => $member->is_admin,
        ]);

        return back()->with('success', 'Rôles mis à jour.');
    }

    public function updateCollaboratorAccess(Request $request, User $member): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        if ($member->trashed()) {
            return back()->with('error', 'Compte archivé — impossible de modifier l’accès collaborateurs.');
        }

        $request->validate([
            'can_manage_collaborator_team_managers' => ['nullable', 'boolean'],
            'collaborator_team_ids' => ['nullable', 'array'],
            'collaborator_team_ids.*' => ['integer', 'exists:collaborator_teams,id'],
        ]);

        $member->update([
            'can_manage_collaborator_team_managers' => $request->boolean('can_manage_collaborator_team_managers'),
        ]);

        if (! $member->isCollaboratorPortalUser()) {
            $member->collaboratorTeams()->detach();
            AdminAudit::record('member.collaborator_access_updated', $member, [
                'team_ids' => [],
                'cleared' => true,
            ]);

            return back()->with('success', 'Accès coordinateur mis à jour. Équipes collaborateur vidées (compte sans rôle collaborateur / admin).');
        }

        $ids = array_map('intval', $request->input('collaborator_team_ids', []));
        $ids = array_values(array_unique(array_filter($ids)));

        $pivot = [];
        $existing = $member->collaboratorTeams()->get()->keyBy('id');

        foreach ($ids as $teamId) {
            $team = $existing->get($teamId);
            $pivot[$teamId] = [
                'is_team_manager' => $team ? (bool) ($team->pivot->is_team_manager ?? false) : false,
            ];
        }

        $member->collaboratorTeams()->sync($pivot);

        AdminAudit::record('member.collaborator_access_updated', $member, [
            'team_ids' => $ids,
            'can_manage_collaborator_team_managers' => $member->can_manage_collaborator_team_managers,
        ]);

        return back()->with('success', 'Équipes collaborateur et coordinateur mis à jour.');
    }

    public function archive(User $member): RedirectResponse
    {
        if ($member->trashed()) {
            return back()->with('error', 'Ce compte est déjà archivé.');
        }

        if ($member->id === auth()->id()) {
            return redirect()
                ->route('portals.settings.account.archive')
                ->with('error', 'Pour archiver votre propre compte, utilisez Réglages → Compte.');
        }

        AdminAudit::record('member.archived', $member, ['email' => $member->email]);
        $member->delete();

        return redirect()
            ->route('admin.members.index', ['status' => 'archived'])
            ->with('success', 'Compte archivé (connexion désactivée, données conservées).');
    }

    public function restore(User $member): RedirectResponse
    {
        if (! $member->trashed()) {
            return back()->with('error', 'Ce compte n’est pas archivé.');
        }

        $member->restore();

        AdminAudit::record('member.restored', $member, ['email' => $member->email]);

        return redirect()
            ->route('admin.members.show', $member)
            ->with('success', 'Compte restauré.');
    }

    public function forceDestroy(Request $request, User $member): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', Rule::in(['SUPPRIMER'])],
        ], [
            'confirmation.in' => 'Tape exactement SUPPRIMER pour confirmer l’effacement définitif.',
        ]);

        if ($member->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas effacer votre propre compte depuis cette page.');
        }

        AdminAudit::record('member.force_deleted', $member, ['email' => $member->email]);
        $member->forceDelete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Compte et données associées supprimés définitivement (RGPD).');
    }
}
