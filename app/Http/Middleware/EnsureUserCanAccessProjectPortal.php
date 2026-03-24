<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessProjectPortal
{
    /**
     * Portail project : admin ou au moins un projet avec droit « voir ».
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user->isAdmin() || $user->hasRole('admin')) {
            return $next($request);
        }

        if (Project::query()->accessibleByNonAdmin($user)->exists()) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'Accès au portail projets refusé.');
    }
}
