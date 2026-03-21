<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAnyRole
{
    /**
     * Autorise l'acces si l'utilisateur a au moins un role requis.
     * Le role admin (ou flag is_admin) passe toujours.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isAdmin() || $user->hasRole('admin')) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($role !== '' && $user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_FORBIDDEN, 'Acces refuse pour ce portail.');
    }
}
