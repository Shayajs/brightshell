<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;
use LogicException;

/**
 * Couche OAuth BrightShield (Passport) sans conflit avec Sanctum HasApiTokens.
 * Utilise $passportAccessToken au lieu de $accessToken (réservé à Sanctum).
 *
 * @phpstan-require-implements \Laravel\Passport\Contracts\OAuthenticatable
 */
trait HasBrightShieldTokens
{
    protected ?ScopeAuthorizable $passportAccessToken = null;

    /**
     * @deprecated Use oauthApps()
     *
     * @return HasMany<\Laravel\Passport\Client, $this>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Passport::clientModel(), 'user_id');
    }

    /**
     * @return MorphMany<\Laravel\Passport\Client, $this>
     */
    public function oauthApps(): MorphMany
    {
        return $this->morphMany(Passport::clientModel(), 'owner');
    }

    /**
     * Jetons OAuth Passport (BrightShield).
     *
     * @return HasMany<\Laravel\Passport\Token, $this>
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Passport::tokenModel(), 'user_id', $this->getAuthIdentifierName())
            ->where(function (Builder $query): void {
                $query->whereHas('client', function (Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $provider = $this->getProviderName();

                        $query->when($provider === config('auth.guards.api.provider'), function (Builder $query): void {
                            $query->orWhereNull('provider');
                        })->orWhere('provider', $provider);
                    });
                });
            });
    }

    public function token(): ?ScopeAuthorizable
    {
        return $this->currentAccessToken();
    }

    public function currentAccessToken(): ?ScopeAuthorizable
    {
        return $this->passportAccessToken;
    }

    public function tokenCan(string $scope): bool
    {
        return $this->passportAccessToken !== null && $this->passportAccessToken->can($scope);
    }

    public function tokenCant(string $scope): bool
    {
        return ! $this->tokenCan($scope);
    }

    /**
     * @param  string[]  $scopes
     */
    public function createToken(string $name, array $scopes = []): PersonalAccessTokenResult
    {
        return app(PersonalAccessTokenFactory::class)->make(
            $this->getAuthIdentifier(), $name, $scopes, $this->getProviderName()
        );
    }

    public function getProviderName(): string
    {
        $providers = collect(config('auth.guards'))->where('driver', 'passport')->pluck('provider')->all();

        foreach (config('auth.providers') as $provider => $config) {
            if (in_array($provider, $providers, true) && $config['driver'] === 'eloquent' && is_a($this, $config['model'])) {
                return $provider;
            }
        }

        throw new LogicException('Unable to determine authentication provider for this model from configuration.');
    }

    /**
     * Affecte le jeton courant (Passport ou Sanctum).
     * Sanctum (auth:sanctum) et Passport (auth:api) appellent tous deux withAccessToken().
     */
    public function withAccessToken(mixed $accessToken): static
    {
        if ($accessToken instanceof ScopeAuthorizable) {
            $this->passportAccessToken = $accessToken;

            return $this;
        }

        // Jeton Sanctum (PersonalAccessToken, TransientToken, …)
        return $this->withSanctumAccessToken($accessToken);
    }
}
