<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('portals.settings.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'profile_notes' => ['nullable', 'string', 'max:10000'],
            'avatar' => ['nullable', 'image', 'max:25600'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $removeAvatar = $request->boolean('remove_avatar');
        unset($data['avatar'], $data['remove_avatar']);

        $user->update($data);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->update(['avatar_path' => $path]);
        } elseif ($removeAvatar) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->update(['avatar_path' => null]);
        }

        $user->refresh();

        return redirect()
            ->route('portals.settings.profile.edit')
            ->with('success', 'Profil enregistré.');
    }
}
