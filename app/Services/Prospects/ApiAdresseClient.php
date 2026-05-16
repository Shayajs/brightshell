<?php

declare(strict_types=1);

namespace App\Services\Prospects;

use App\Services\Prospects\Scoring\Dto\GeocodedAddress;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;

/**
 * Client de l'API « Adresse » (Base Adresse Nationale).
 *
 * - Aucune auth
 * - Limite : 50 req/s
 * - Retourne (lat, long, ville, code_postal, code_insee_commune)
 *
 * @see https://api-adresse.data.gouv.fr/
 */
final class ApiAdresseClient extends AbstractGouvApiClient
{
    public function __construct()
    {
        parent::__construct(
            baseUrl: 'https://api-adresse.data.gouv.fr',
            timeout: 10,
            throttleMicroseconds: (int) config('prospects.throttle_us.adresse', 25_000),
        );
    }

    /**
     * Géocode une adresse libre. Résultats cachés pendant `geocoding_cache_ttl_days`.
     */
    public function geocode(string $query, ?string $codePostal = null): ?GeocodedAddress
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $ttl = (int) config('prospects.enrichment.geocoding_cache_ttl_days', 30) * 86_400;
        $cacheKey = 'prospects:geocode:'.md5($query.'|'.($codePostal ?? ''));

        return Cache::remember($cacheKey, $ttl, function () use ($query, $codePostal): ?GeocodedAddress {
            $this->throttle();

            try {
                $params = ['q' => $query, 'limit' => 1, 'autocomplete' => 0];
                if ($codePostal !== null && $codePostal !== '') {
                    $params['postcode'] = $codePostal;
                }

                $response = $this->http()->get('/search/', $params)->throw();
            } catch (RequestException $e) {
                $this->logFailure('geocode', $e, ['query' => $query]);

                return null;
            }

            $features = $response->json('features') ?? [];
            if ($features === []) {
                return null;
            }

            $first = $features[0];
            $coords = $first['geometry']['coordinates'] ?? null; // [long, lat]
            $props = $first['properties'] ?? [];

            if (! is_array($coords) || count($coords) < 2) {
                return null;
            }

            return new GeocodedAddress(
                latitude: (float) $coords[1],
                longitude: (float) $coords[0],
                codeInseeCommune: isset($props['citycode']) ? (string) $props['citycode'] : null,
                ville: isset($props['city']) ? (string) $props['city'] : null,
                codePostal: isset($props['postcode']) ? (string) $props['postcode'] : null,
                score: (float) ($props['score'] ?? 0),
            );
        });
    }
}
