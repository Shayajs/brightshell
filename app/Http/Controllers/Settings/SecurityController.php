<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $sessions = collect();

        if (config('session.driver') === 'database') {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->limit(15)
                ->get();
        }

        return view('portals.settings.security', [
            'user' => $user,
            'sessions' => $sessions,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()
            ->route('portals.settings.security.edit')
            ->with('success', 'Mot de passe mis à jour.');
    }

    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        if (config('session.driver') !== 'database') {
            return back()->with('error', 'Les sessions ne sont pas stockées en base sur cette instance.');
        }

        $currentId = session()->getId();

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $currentId)
            ->delete();

        return back()->with('success', 'Les autres sessions ont été déconnectées.');
    }
}
