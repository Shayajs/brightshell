<?php

namespace Tests\Feature\BrightShield;

use App\Models\BrightshieldUserConsent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class UserConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_is_recorded_and_allows_skip_on_next_authorization(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            'Futurmeal',
            ['https://futurmeal.test/auth/brightshield/callback'],
        );

        BrightshieldUserConsent::record($user, (string) $client->getKey(), ['openid', 'profile', 'email']);

        $this->assertTrue(
            BrightshieldUserConsent::hasGranted($user, (string) $client->getKey(), [])
        );
    }

    public function test_consent_revocation_removes_access(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            'Futurmeal',
            ['https://futurmeal.test/auth/brightshield/callback'],
        );

        BrightshieldUserConsent::record($user, (string) $client->getKey(), ['openid']);
        BrightshieldUserConsent::revoke($user, (string) $client->getKey());

        $this->assertFalse(
            BrightshieldUserConsent::hasGranted($user, (string) $client->getKey(), [])
        );
    }
}
