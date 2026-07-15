<?php

namespace App\Http\Controllers\BrightShield;

use App\Support\BrightshellDomain;
use Illuminate\Http\JsonResponse;

class OpenIdConfigurationController
{
    public function __invoke(): JsonResponse
    {
        $base = BrightshellDomain::shieldUrl();

        return response()->json([
            'issuer' => $base,
            'authorization_endpoint' => $base.'/oauth/authorize',
            'token_endpoint' => $base.'/oauth/token',
            'userinfo_endpoint' => $base.'/oauth/userinfo',
            'jwks_uri' => $base.'/oauth/jwks',
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => array_keys(config('brightshield.scopes', [])),
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post'],
            'claims_supported' => [
                'sub',
                'email',
                'email_verified',
                'name',
                'given_name',
                'family_name',
                'picture',
            ],
            'code_challenge_methods_supported' => ['S256'],
        ]);
    }
}
