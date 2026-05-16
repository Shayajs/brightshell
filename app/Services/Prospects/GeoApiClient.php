<?php

declare(strict_types=1);

namespace App\Services\Prospects;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;

/**
 * Client de l'API « Découpage Administratif » (geo.api.gouv.fr).
 *
 * Utilisé par l'UI pour peupler les cascades Région → Département → Commune.
 * Tout est caché 7 jours par défaut.
 *
 * @see https://geo.api.gouv.fr/decoupage-administratif
 */
final class GeoApiClient extends AbstractGouvApiClient
{
    public function __construct()
    {
        parent::__construct(
            baseUrl: 'https://geo.api.gouv.fr',
            timeout: 10,
            throttleMicroseconds: (int) config('prospects.throttle_us.geo', 25_000),
        );
    }

    /**
     * @return list<array{code: string, nom: string}>
     */
    public function regions(): array
    {
        return $this->cached('regions', '/regions', ['fields' => 'nom,code']);
    }

    /**
     * @return list<array{code: string, nom: string}>
     */
    public function departementsParRegion(string $codeRegion): array
    {
        if ($codeRegion === '') {
            return [];
        }

        return $this->cached('dep:'.$codeRegion, "/regions/{$codeRegion}/departements", ['fields' => 'nom,code']);
    }

    /**
     * @return list<array{code: string, nom: string, codesPostaux: list<string>}>
     */
    public function communesParDepartement(string $codeDep): array
    {
        if ($codeDep === '') {
            return [];
        }

        return $this->cached('com:'.$codeDep, "/departements/{$codeDep}/communes", ['fields' => 'nom,code,codesPostaux']);
    }

    /**
     * Recherche libre de communes (autocomplétion).
     *
     * @return list<array{code: string, nom: string, codesPostaux: list<string>}>
     */
    public function searchCommunes(string $nom): array
    {
        $nom = trim($nom);
        if (mb_strlen($nom) < 2) {
            return [];
        }

        return $this->cached('com_search:'.mb_strtolower($nom), '/communes', [
            'nom' => $nom,
            'fields' => 'nom,code,codesPostaux',
            'limit' => 15,
            'boost' => 'population',
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function cached(string $key, string $endpoint, array $params): array
    {
        $ttl = (int) config('prospects.enrichment.geo_api_cache_ttl_days', 7) * 86_400;

        return Cache::remember("prospects:geo:{$key}", $ttl, function () use ($endpoint, $params): array {
            $this->throttle();
            try {
                $response = $this->http()->get($endpoint, $params)->throw();
            } catch (RequestException $e) {
                $this->logFailure($endpoint, $e);

                return [];
            }

            $data = $response->json() ?? [];

            return is_array($data) ? $data : [];
        });
    }
}
