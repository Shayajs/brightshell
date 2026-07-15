<?php

namespace Tests\Feature\BrightShield;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class OpenIdDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_openid_configuration_is_available_on_shield_host(): void
    {
        $host = $this->shieldHost();

        $response = $this->withHeader('Host', $host)
            ->get('/.well-known/openid-configuration');

        $response->assertOk()
            ->assertJsonPath('issuer', 'https://'.$host)
            ->assertJsonPath('authorization_endpoint', 'https://'.$host.'/oauth/authorize')
            ->assertJsonPath('token_endpoint', 'https://'.$host.'/oauth/token')
            ->assertJsonPath('userinfo_endpoint', 'https://'.$host.'/oauth/userinfo')
            ->assertJsonPath('scopes_supported', array_keys(config('brightshield.scopes')));
    }

    public function test_web_routes_are_blocked_on_shield_host(): void
    {
        $host = $this->shieldHost();

        $this->withHeader('Host', $host)
            ->get('/')
            ->assertNotFound();
    }

    public function test_userinfo_requires_bearer_token(): void
    {
        $host = $this->shieldHost();

        $this->withHeader('Host', $host)
            ->getJson('/oauth/userinfo')
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

        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            'Futurmeal',
            ['https://futurmeal.test/auth/brightshield/callback'],
        );

        $token = $user->createToken('Test', ['openid', 'profile', 'email']);

        $this->withHeader('Host', $host)
            ->withHeader('Authorization', 'Bearer '.$token->accessToken)
            ->getJson('/oauth/userinfo')
            ->assertOk()
            ->assertJsonPath('sub', (string) $user->id)
            ->assertJsonPath('email', 'ada@example.test')
            ->assertJsonPath('given_name', 'Ada');
    }

    public function test_unverified_user_cannot_access_userinfo(): void
    {
        $host = $this->shieldHost();
        $user = User::factory()->unverified()->create();

        $token = $user->createToken('Test', ['openid', 'email']);

        $this->withHeader('Host', $host)
            ->withHeader('Authorization', 'Bearer '.$token->accessToken)
            ->getJson('/oauth/userinfo')
            ->assertForbidden();
    }

    private function shieldHost(): string
    {
        $host = (string) config('brightshell.domains.shield_host', '');
        if ($host !== '') {
            return $host;
        }

        $root = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return 'shield.'.$root;
    }
}
