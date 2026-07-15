<?php

namespace App\Services\BrightShield;

use Illuminate\Http\Request;
use Laravel\Passport\Client;

/**
 * Résout et valide l’icône de l’application cliente (query app_icon).
 * Seules les URLs dont l’hôte correspond aux redirect_uris du client
 * (ou à une allowlist config) sont acceptées — anti-XSS / hotlink abusif.
 */
final class ClientIconResolver
{
    public function resolve(Request $request, Client $client, ?string $configFallback = null): ?string
    {
        $candidate = trim((string) $request->query('app_icon', ''));
        if ($candidate === '') {
            $candidate = trim((string) ($configFallback ?? ''));
        }

        if ($candidate === '') {
            return null;
        }

        if (! $this->isAllowed($candidate, $client)) {
            return null;
        }

        return $candidate;
    }

    public function isAllowed(string $url, Client $client): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['https', 'http'], true)) {
            return false;
        }

        // En prod on refuse le plain HTTP sauf pour les domaines .test
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '') {
            return false;
        }

        if ($scheme === 'http' && ! str_ends_with($host, '.test') && $host !== 'localhost') {
            return false;
        }

        $allowed = $this->allowedHosts($client);

        return in_array($host, $allowed, true);
    }

    /**
     * @return list<string>
     */
    private function allowedHosts(Client $client): array
    {
        $hosts = [];

        foreach ($client->redirect_uris ?? [] as $uri) {
            $host = parse_url((string) $uri, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $hosts[] = strtolower($host);
            }
        }

        $key = strtolower((string) $client->name);
        $extra = config('brightshield.client_labels.'.$key.'.icon_hosts', []);
        if (is_array($extra)) {
            foreach ($extra as $host) {
                $hosts[] = strtolower(trim((string) $host));
            }
        }

        $fallbackIcon = config('brightshield.client_labels.'.$key.'.icon_url');
        if (is_string($fallbackIcon) && $fallbackIcon !== '') {
            $host = parse_url($fallbackIcon, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $hosts[] = strtolower($host);
            }
        }

        return array_values(array_unique(array_filter($hosts)));
    }
}
