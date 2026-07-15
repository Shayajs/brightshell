<?php

namespace Tests\Feature\BrightShield;

use App\Models\BrightshieldUserConsent;
use App\Models\User;
use App\Support\BrightshellDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

/**
 * La session BrightShell (cookie partagé .{root}) vaut connexion BrightShield :
 * pas de re-saisie de mot de passe sur shield.*.
 */
class SsoSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_account_login(): void
    {
        $client = $this->makeClient();

        $this->getOnHost($this->shieldHost(), '/oauth/authorize?'.$this->authorizeQuery($client))
            ->assertRedirectContains('/login');
    }

    public function test_logged_in_user_reaches_consent_without_password(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $client = $this->makeClient();

        $this->actingAs($user)
            ->getOnHost($this->shieldHost(), '/oauth/authorize?'.$this->authorizeQuery($client))
            ->assertOk()
            ->assertSee('Autoriser')
            ->assertSee($user->email);
    }

    public function test_logged_in_user_with_prior_consent_skips_consent_screen(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $client = $this->makeClient();

        BrightshieldUserConsent::record($user, (string) $client->getKey(), ['openid', 'profile', 'email']);

        $response = $this->actingAs($user)
            ->getOnHost($this->shieldHost(), '/oauth/authorize?'.$this->authorizeQuery($client));

        $response->assertRedirect();
        $this->assertStringStartsWith(
            'https://futurmeal.test/auth/brightshield/callback?code=',
            $response->headers->get('Location'),
        );
    }

    public function test_unverified_user_is_sent_to_email_verification(): void
    {
        $user = User::factory()->unverified()->create();
        $client = $this->makeClient();

        $this->actingAs($user)
            ->getOnHost($this->shieldHost(), '/oauth/authorize?'.$this->authorizeQuery($client))
            ->assertRedirect(route('verification.notice'));
    }

    private function makeClient(): Client
    {
        return app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            'Futurmeal',
            ['https://futurmeal.test/auth/brightshield/callback'],
        );
    }

    private function authorizeQuery(Client $client): string
    {
        return http_build_query([
            'client_id' => $client->getKey(),
            'redirect_uri' => 'https://futurmeal.test/auth/brightshield/callback',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => 'test-state',
        ]);
    }

    private function shieldHost(): string
    {
        return BrightshellDomain::effectiveShieldHost();
    }
}
