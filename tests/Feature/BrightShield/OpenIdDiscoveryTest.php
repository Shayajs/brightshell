<?php

namespace Tests\Feature\BrightShield;

use App\Models\User;
use App\Support\BrightshellDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class OpenIdDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_openid_configuration_is_available_on_shield_host(): void
    {
        $host = $this->shieldHost();

        $this->getOnHost($host, '/.well-known/openid-configuration')
            ->assertOk()
            ->assertJsonPath('issuer', 'https://'.$host)
            ->assertJsonPath('authorization_endpoint', 'https://'.$host.'/oauth/authorize')
            ->assertJsonPath('token_endpoint', 'https://'.$host.'/oauth/token')
            ->assertJsonPath('userinfo_endpoint', 'https://'.$host.'/oauth/userinfo')
            ->assertJsonPath('scopes_supported', array_keys(config('brightshield.scopes')));
    }

    public function test_web_routes_are_blocked_on_shield_host(): void
    {
        $this->getOnHost($this->shieldHost(), '/')
            ->assertNotFound();
    }

    public function test_userinfo_requires_bearer_token(): void
    {
        $this->getJsonOnHost($this->shieldHost(), '/oauth/userinfo')
            ->assertUnauthorized();
    }

    public function test_userinfo_returns_claims_for_authenticated_token(): void
    {
        $host = $this->shieldHost();
        $user = User::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'email_verified_at' => now(),
        ]);

        app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            'Futurmeal',
            ['https://futurmeal.test/auth/brightshield/callback'],
        );

        $token = $user->createToken('Test', ['openid', 'profile', 'email']);

        $this->getJsonOnHost($host, '/oauth/userinfo', [
            'Authorization' => 'Bearer '.$token->accessToken,
        ])
            ->assertOk()
            ->assertJsonPath('sub', (string) $user->id)
            ->assertJsonPath('email', 'ada@example.test')
            ->assertJsonPath('given_name', 'Ada');
    }

    public function test_unverified_user_cannot_access_userinfo(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('Test', ['openid', 'email']);

        $this->getJsonOnHost($this->shieldHost(), '/oauth/userinfo', [
            'Authorization' => 'Bearer '.$token->accessToken,
        ])->assertForbidden();
    }

    private function shieldHost(): string
    {
        return BrightshellDomain::effectiveShieldHost();
    }
}
