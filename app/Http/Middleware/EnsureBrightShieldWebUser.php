<?php

namespace App\Http\Middleware;

use App\Support\BrightshellAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Flux web BrightShield (/oauth/authorize) : la session BrightShell vaut
 * connexion BrightShield. Les invités passent (Passport redirige vers le
 * login account.*) ; les comptes non vérifiés ou archivés sont bloqués.
 */
class EnsureBrightShieldWebUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->trashed()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();

            return redirect()->to(BrightshellAccount::loginUrl());
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
