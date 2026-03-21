<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\RoleResolver;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Racine du sous-domaine account.* : redirection login ou portail du rôle le plus élevé.
     */
    public function __invoke(): RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->intended(RoleResolver::defaultPortalUrl(auth()->user()));
        }

        return redirect()->route('login');
    }
}
