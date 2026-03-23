<?php

namespace App\Http\Middleware;

use App\Support\AdminEmailVerification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdminPortal
{
    /**
     * Accès portail admin : rôle admin ou flag is_admin (héritage).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || (! $user->hasRole('admin') && ! $user->isAdmin())) {
            abort(Response::HTTP_FORBIDDEN, 'Accès administration refusé.');
        }

        AdminEmailVerification::ensureVerifiedIfAdmin($user);

        return $next($request);
    }
}
