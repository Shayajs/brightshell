<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\BrightshellDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorise l’embarquement cross-sous-domaines (iframe notification bridge).
 *
 * Nginx envoie `X-Frame-Options: SAMEORIGIN`, ce qui bloque settings.* dans une
 * iframe depuis prospects.* / admin.*. On retire XFO et on pose un CSP
 * `frame-ancestors` couvrant tous les portails *.domain.
 */
final class AllowBrightshellPortalFraming
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $root = BrightshellDomain::effectiveRoot();
        if ($root === '') {
            return $response;
        }

        $scheme = BrightshellDomain::urlScheme();
        $ancestors = "'self' {$scheme}://{$root} {$scheme}://*.{$root}";

        $response->headers->remove('X-Frame-Options');
        $response->headers->set('Content-Security-Policy', "frame-ancestors {$ancestors}");

        return $response;
    }
}
