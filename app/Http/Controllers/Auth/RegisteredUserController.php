<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'member_role' => ['required', 'string', Rule::in(['client', 'student', 'collaborator'])],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'member_role.required' => 'Choisissez un type de compte.',
            'member_role.in' => 'Type de compte invalide.',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->forceFill([
            'email_reverse_verification_token' => Str::random(40),
        ])->save();

        $role = Role::query()->where('slug', $validated['member_role'])->firstOrFail();
        $user->roles()->attach($role->id);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
