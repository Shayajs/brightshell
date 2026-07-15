<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Laravel\Passport\Http\Responses\SimpleViewResponse;
use Laravel\Passport\Passport;

class BrightShieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Avant le boot de PassportServiceProvider : seules nos routes shield.* existent.
        Passport::ignoreRoutes();

        $this->app->when([
            PassportAuthorizationController::class,
            \App\Http\Controllers\BrightShield\AuthorizationController::class,
        ])->needs(StatefulGuard::class)->give(fn () => Auth::guard(config('passport.guard', 'web')));
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addMinutes((int) config('brightshield.access_token_ttl_minutes', 60)));
        Passport::refreshTokensExpireIn(now()->addDays((int) config('brightshield.refresh_token_ttl_days', 30)));

        Passport::tokensCan(config('brightshield.scopes', []));
        Passport::defaultScopes([]);

        Passport::authorizationView(function (array $parameters): AuthorizationViewResponse {
            /** @var \Laravel\Passport\Client $client */
            $client = $parameters['client'];
            $clientKey = strtolower((string) $client->name);
            $labels = config('brightshield.client_labels.'.$clientKey, []);

            $scopeIds = collect($parameters['scopes'] ?? [])
                ->map(fn ($scope) => $scope->id)
                ->values()
                ->all();

            $sharedData = app(\App\Services\BrightShield\UserClaimsBuilder::class)
                ->preview($parameters['user'], $scopeIds);

            /** @var \Illuminate\Http\Request $request */
            $request = $parameters['request'] ?? request();

            $appIconUrl = app(\App\Services\BrightShield\ClientIconResolver::class)->resolve(
                $request,
                $client,
                is_string($labels['icon_url'] ?? null) ? $labels['icon_url'] : null,
            );

            return (new SimpleViewResponse('brightshield.consent'))
                ->withParameters([
                    ...$parameters,
                    'clientLabel' => $labels,
                    'sharedData' => $sharedData,
                    'appIconUrl' => $appIconUrl,
                ]);
        });
    }
}
