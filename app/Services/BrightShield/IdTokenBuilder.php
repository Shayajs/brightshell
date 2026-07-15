<?php

namespace App\Services\BrightShield;

use App\Models\User;
use App\Support\BrightshellDomain;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

final class IdTokenBuilder
{
    /**
     * @param  list<string>  $scopes
     */
    public function build(User $user, string $clientId, array $scopes, ?string $nonce = null): string
    {
        $claims = app(UserClaimsBuilder::class)->build($user, $scopes);

        $issuer = BrightshellDomain::shieldUrl();
        $now = new DateTimeImmutable;

        $config = Configuration::forAsymmetricSigner(
            new Sha256,
            InMemory::file(storage_path('oauth-private.key')),
            InMemory::file(storage_path('oauth-public.key')),
        );

        $builder = $config->builder()
            ->issuedBy($issuer)
            ->permittedFor($clientId)
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+'.config('brightshield.access_token_ttl_minutes', 60).' minutes'))
            ->relatedTo((string) $user->getAuthIdentifier())
            ->withClaim('email', $claims['email'] ?? null)
            ->withClaim('email_verified', $claims['email_verified'] ?? false);

        if (isset($claims['name'])) {
            $builder = $builder->withClaim('name', $claims['name']);
        }

        if (isset($claims['given_name'])) {
            $builder = $builder->withClaim('given_name', $claims['given_name']);
        }

        if (isset($claims['family_name'])) {
            $builder = $builder->withClaim('family_name', $claims['family_name']);
        }

        if (isset($claims['picture'])) {
            $builder = $builder->withClaim('picture', $claims['picture']);
        }

        if ($nonce !== null && $nonce !== '') {
            $builder = $builder->withClaim('nonce', $nonce);
        }

        return $builder->getToken($config->signer(), $config->signingKey())->toString();
    }
}
