<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\BrightshellAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountClosureController extends Controller
{
    public function edit(): View
    {
        return view('portals.settings.account-archive');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirm_archive' => ['accepted'],
        ], [
            'confirm_archive.accepted' => 'Vous devez cocher la case de confirmation.',
        ]);

        $user = $request->user();
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->to(BrightshellAccount::loginUrl())
            ->with('success', 'Votre compte a été archivé. L’adresse e-mail est libérée : vous pouvez vous inscrire à nouveau avec la même adresse pour vos tests.');
    }
}
