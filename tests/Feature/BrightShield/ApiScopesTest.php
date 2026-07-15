<?php

namespace Tests\Feature\BrightShield;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_returns_only_scoped_data(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '+33612345678',
        ]);

        $token = $user->createToken('Test', ['openid', 'email']);

        $this->withHeader('Host', $this->apiHost())
            ->withHeader('Authorization', 'Bearer '.$token->accessToken)
            ->getJson('/v1/brightshield/me')
            ->assertOk()
            ->assertJsonPath('email', $user->email)
            ->assertJsonMissingPath('phone_number')
            ->assertJsonMissingPath('given_name');
    }

    public function test_scoped_endpoint_rejects_missing_scope(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '+33612345678',
        ]);

        $token = $user->createToken('Test', ['openid', 'email']);

        $this->withHeader('Host', $this->apiHost())
            ->withHeader('Authorization', 'Bearer '.$token->accessToken)
            ->getJson('/v1/brightshield/me/telephone')
            ->assertForbidden();
    }

    public function test_phone_endpoint_returns_phone_with_scope(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '+33612345678',
        ]);

        $token = $user->createToken('Test', ['phone']);

        $this->withHeader('Host', $this->apiHost())
            ->withHeader('Authorization', 'Bearer '.$token->accessToken)
            ->getJson('/v1/brightshield/me/telephone')
            ->assertOk()
            ->assertJsonPath('phone_number', '+33612345678');
    }

    public function test_sanctum_private_api_rejects_passport_token(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $token = $user->createToken('Test', ['openid', 'email', 'profile']);

        $this->withHeader('Host', $this->apiHost())
            ->withHeader('Authorization', 'Bearer '.$token->accessToken)
            ->getJson('/v1/me')
            ->assertUnauthorized();
    }

    private function apiHost(): string
    {
        $host = (string) config('brightshell.domains.api_host', '');
        if ($host !== '') {
            return $host;
        }

        $root = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return 'api.'.$root;
    }
}
