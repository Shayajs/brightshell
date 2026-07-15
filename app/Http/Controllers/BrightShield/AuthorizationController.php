<?php

namespace App\Http\Controllers\BrightShield;

use App\Models\BrightshieldUserConsent;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;

class AuthorizationController extends PassportAuthorizationController
{
    /**
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    protected function hasGrantedScopes(Authenticatable $user, Client $client, array $scopes): bool
    {
        if (parent::hasGrantedScopes($user, $client, $scopes)) {
            return true;
        }

        if (! $user instanceof \App\Models\User) {
            return false;
        }

        return BrightshieldUserConsent::hasGranted(
            $user,
            (string) $client->getKey(),
            $scopes,
        );
    }
}
