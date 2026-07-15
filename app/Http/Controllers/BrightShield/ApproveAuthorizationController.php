<?php

namespace App\Http\Controllers\BrightShield;

use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController as PassportApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\RetrievesAuthRequestFromSession;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ApproveAuthorizationController extends PassportApproveAuthorizationController
{
    public function __construct(
        AuthorizationServer $server,
    ) {
        parent::__construct($server);
    }

    public function approve(Request $request, ResponseInterface $psrResponse): Response
    {
        $authRequest = $this->getAuthRequestFromSession($request);
        $user = $request->user();

        $response = parent::approve($request, $psrResponse);

        if ($user instanceof \App\Models\User && $response->isRedirect()) {
            $scopeIds = collect($authRequest->getScopes())
                ->map(fn ($scope) => $scope->getIdentifier())
                ->values()
                ->all();

            \App\Models\BrightshieldUserConsent::record(
                $user,
                $authRequest->getClient()->getIdentifier(),
                $scopeIds,
            );
        }

        return $response;
    }
}
