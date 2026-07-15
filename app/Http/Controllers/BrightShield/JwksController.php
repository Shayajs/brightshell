<?php

namespace App\Http\Controllers\BrightShield;

use App\Support\BrightshellDomain;
use Illuminate\Http\Response;

class JwksController
{
    public function __invoke(): Response
    {
        $publicKey = file_get_contents(storage_path('oauth-public.key'));

        if ($publicKey === false) {
            abort(503, 'Clés OAuth indisponibles.');
        }

        $details = openssl_pkey_get_details(openssl_pkey_get_public($publicKey));
        $jwk = [
            'kty' => 'RSA',
            'use' => 'sig',
            'alg' => 'RS256',
            'n' => rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '='),
            'e' => rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '='),
        ];

        return response()->json([
            'keys' => [$jwk],
        ]);
    }
}
