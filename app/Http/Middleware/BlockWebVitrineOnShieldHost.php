<?php

namespace App\Http\Middleware;

use App\Support\BrightshellDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Les routes vitrine (sans contrainte de domaine) ne doivent pas répondre sur l’hôte BrightShield.
 * Empêche tout contournement du shield via les routes web classiques.
 */
final class BlockWebVitrineOnShieldHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $shieldHost = BrightshellDomain::effectiveShieldHost();
        if ($shieldHost !== '' && strcasecmp($request->getHost(), $shieldHost) === 0) {
            return response()->json([
                'message' => 'Cet hôte est réservé à BrightShield (OAuth2 / OIDC). Utilisez /oauth/… ou /.well-known/openid-configuration.',
            ], 404);
        }

        return $next($request);
    }
}
