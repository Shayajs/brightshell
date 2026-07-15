<?php

namespace App\Http\Controllers\BrightShield;

use App\Models\BrightshieldUserConsent;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\Bridge\User as PassportUser;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController as PassportApproveAuthorizationController;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
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
        // Lire sans pull : parent::approve() consommera authToken / authRequest.
        $authRequest = $this->peekAuthRequest($request);
        $user = $request->user();

        $response = parent::approve($request, $psrResponse);

        if (
            $authRequest instanceof AuthorizationRequestInterface
            && $user instanceof User
            && $response->isRedirect()
        ) {
            $scopeIds = collect($authRequest->getScopes())
                ->map(fn ($scope) => $scope->getIdentifier())
                ->values()
                ->all();

            BrightshieldUserConsent::record(
                $user,
                $authRequest->getClient()->getIdentifier(),
                $scopeIds,
            );
        }

        return $response;
    }

    private function peekAuthRequest(Request $request): ?AuthorizationRequestInterface
    {
        if (
            $request->isNotFilled('auth_token')
            || $request->session()->get('authToken') !== $request->input('auth_token')
            || ! $request->session()->has('authRequest')
        ) {
            return null;
        }

        $authRequest = unserialize($request->session()->get('authRequest'), [
            'allowed_classes' => [
                AuthorizationRequest::class,
                Client::class,
                Scope::class,
                PassportUser::class,
            ],
        ]);

        return $authRequest instanceof AuthorizationRequestInterface ? $authRequest : null;
    }
}
