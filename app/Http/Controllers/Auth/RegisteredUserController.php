<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvitation;
use App\Models\Role;
use App\Models\User;
use App\Services\ProjectInvitationAcceptor;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $projectInvitation = null;
        $token = $request->query('project_invitation');
        if (is_string($token) && $token !== '') {
            $projectInvitation = ProjectInvitation::query()
                ->where('token', $token)
                ->whereNull('accepted_at')
                ->with('project')
                ->first();
            if ($projectInvitation?->isExpired()) {
                $projectInvitation = null;
            }
        }

        return view('auth.register', compact('projectInvitation'));
    }

    public function store(Request $request, ProjectInvitationAcceptor $invitationAcceptor): RedirectResponse
    {
        $invitationToken = $request->input('project_invitation_token');
        $invitation = null;
        if (is_string($invitationToken) && $invitationToken !== '') {
            $invitation = ProjectInvitation::query()
                ->where('token', $invitationToken)
                ->whereNull('accepted_at')
                ->first();
            if ($invitation === null || $invitation->isExpired()) {
                throw ValidationException::withMessages([
                    'email' => 'Invitation invalide ou expirée.',
                ]);
            }
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'member_role' => [
                Rule::excludeIf($invitation !== null),
                Rule::requiredIf($invitation === null),
                'string',
                Rule::in(['client', 'student', 'collaborator']),
            ],
            'project_invitation_token' => ['nullable', 'string'],
        ];

        $validated = $request->validate($rules, [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'member_role.required' => 'Choisissez un type de compte.',
            'member_role.in' => 'Type de compte invalide.',
        ]);

        if ($invitation !== null && strcasecmp($validated['email'], $invitation->email) !== 0) {
            throw ValidationException::withMessages([
                'email' => 'Utilisez l’adresse e-mail indiquée dans l’invitation ('.$invitation->email.').',
            ]);
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->forceFill([
            'email_reverse_verification_token' => Str::random(40),
        ])->save();

        $roleSlug = $invitation !== null ? 'client' : (string) $validated['member_role'];
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role->id);

        if ($invitation !== null) {
            $invitationAcceptor->accept($invitation, $user);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
