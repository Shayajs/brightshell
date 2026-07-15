<?php

namespace App\Http\Controllers\BrightShield;

use App\Models\User;
use App\Services\BrightShield\IdTokenBuilder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenController extends PassportAccessTokenController
{
    public function __construct(
        AuthorizationServer $server,
        protected IdTokenBuilder $idTokenBuilder,
    ) {
        parent::__construct($server);
    }

    public function issueToken(ServerRequestInterface $psrRequest, ResponseInterface $psrResponse): Response
    {
        $createdToken = null;

        $listener = function (AccessTokenCreated $event) use (&$createdToken): void {
            $createdToken = $event;
        };

        Event::listen(AccessTokenCreated::class, $listener);

        try {
            $response = parent::issueToken($psrRequest, $psrResponse);
        } finally {
            Event::forget(AccessTokenCreated::class);
        }

        if ($response->getStatusCode() !== 200 || $createdToken === null) {
            return $response;
        }

        $payload = json_decode($response->getContent(), true);
        if (! is_array($payload)) {
            return $response;
        }

        $parsedBody = $psrRequest->getParsedBody();
        $scopeString = is_array($parsedBody) ? (string) ($parsedBody['scope'] ?? '') : '';
        $scopes = array_values(array_filter(explode(' ', $scopeString)));

        if (! in_array('openid', $scopes, true) || ! File::exists(storage_path('oauth-private.key'))) {
            return $response;
        }

        $user = User::query()->find($createdToken->userId);
        if ($user === null) {
            return $response;
        }

        $nonce = is_array($parsedBody) ? (string) ($parsedBody['nonce'] ?? '') : '';

        try {
            $payload['id_token'] = $this->idTokenBuilder->build(
                $user,
                $createdToken->clientId,
                $scopes,
                $nonce !== '' ? $nonce : null,
            );
        } catch (\Throwable) {
            return $response;
        }

        return response()->json($payload, 200, $response->headers->all());
    }
}
