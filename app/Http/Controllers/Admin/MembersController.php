<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MembersController extends Controller
{
    public function index(): View
    {
        $members = User::with('roles')->orderByDesc('id')->paginate(25);

        return view('admin.members.index', compact('members'));
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
            'members.*.name' => ['required', 'string', 'max:255'],
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
                'name' => $m['name'],
                'email' => $m['email'],
                'password' => Hash::make($raw),
                'is_admin' => ! empty($m['is_admin']),
            ]);

            if (! empty($m['roles'])) {
                $member->roles()->sync($m['roles']);
            }

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

    public function show(User $member): View
    {
        $member->load('roles', 'companies');
        $allRoles = Role::orderBy('priority', 'desc')->get();

        return view('admin.members.show', compact('member', 'allRoles'));
    }

    public function updateRoles(Request $request, User $member): RedirectResponse
    {
        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $member->roles()->sync($request->input('roles', []));

        if ($request->has('is_admin')) {
            $member->update(['is_admin' => (bool) $request->input('is_admin')]);
        }

        return back()->with('success', 'Rôles mis à jour.');
    }
}
