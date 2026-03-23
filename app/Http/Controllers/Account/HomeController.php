<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\AdminEmailVerification;
use App\Support\RoleResolver;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Racine du sous-domaine account.* : redirection login ou portail du rôle le plus élevé.
     */
    public function __invoke(): RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();
            AdminEmailVerification::ensureVerifiedIfAdmin($user);
            if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended(RoleResolver::defaultPortalUrl($user));
        }

        return redirect()->route('login');
    }
}
