<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasDeveloperRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasRole('developer')) {
            abort(Response::HTTP_FORBIDDEN, 'Accès réservé aux comptes développeur.');
        }

        return $next($request);
    }
}
