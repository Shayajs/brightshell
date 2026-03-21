<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CORS minimal pour GET public (partenaires / intégrations front).
 */
final class PublicApiCors
{
    private const HEADERS = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Max-Age' => '86400',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders(self::HEADERS);
        }

        $response = $next($request);

        foreach (self::HEADERS as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
