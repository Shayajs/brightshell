<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! File::exists(storage_path('oauth-private.key'))) {
            Artisan::call('passport:keys', ['--force' => true]);
        }

        $this->ensurePassportPersonalAccessClient();
    }

    protected function ensurePassportPersonalAccessClient(): void
    {
        $exists = Client::query()->get()->contains(
            static fn (Client $client): bool => $client->hasGrantType('personal_access'),
        );

        if ($exists) {
            return;
        }

        app(ClientRepository::class)->createPersonalAccessGrantClient(
            name: 'BrightShield Testing',
            provider: 'users',
        );
    }

    /**
     * Requête sur un hôte de domaine Laravel (shield.*, api.*) — le Host seul
     * ne suffît pas toujours : l’URI absolue fixe HTTP_HOST + domaine de route.
     */
    protected function getOnHost(string $host, string $uri, array $headers = []): TestResponse
    {
        $url = 'https://'.$host.'/'.ltrim($uri, '/');

        return $this->withHeaders($headers)->get($url);
    }

    protected function getJsonOnHost(string $host, string $uri, array $headers = []): TestResponse
    {
        $url = 'https://'.$host.'/'.ltrim($uri, '/');

        return $this->withHeaders($headers)->getJson($url);
    }
}
