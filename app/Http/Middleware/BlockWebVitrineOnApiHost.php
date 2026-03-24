<?php

namespace App\Http\Middleware;

use App\Support\BrightshellDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Les routes vitrine (sans contrainte de domaine) ne doivent pas répondre sur l’hôte API.
 */
final class BlockWebVitrineOnApiHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiHost = BrightshellDomain::effectiveApiHost();
        if ($apiHost !== '' && strcasecmp($request->getHost(), $apiHost) === 0) {
            return response()->json([
                'message' => 'Cet hôte est réservé à l’API JSON. Utilisez les chemins /v1/… (documentation : docs/API_PRIVEE_V1.md).',
            ], 404);
        }

        return $next($request);
    }
}
