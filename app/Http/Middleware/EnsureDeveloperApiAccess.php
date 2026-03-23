<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API réservée aux comptes avec rôle developer, e-mail vérifié, non archivés.
 */
class EnsureDeveloperApiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if ($user->trashed()) {
            return response()->json(['message' => 'Compte indisponible.'], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Adresse e-mail non vérifiée.'], 403);
        }

        if (! $user->hasRole('developer')) {
            return response()->json(['message' => 'Rôle développeur requis pour l’API.'], 403);
        }

        return $next($request);
    }
}
