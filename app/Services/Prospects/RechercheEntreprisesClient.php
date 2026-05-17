<?php

declare(strict_types=1);

namespace App\Services\Prospects;

use Generator;
use Illuminate\Http\Client\RequestException;

/**
 * Client de l'API publique « Recherche Entreprises » (data.gouv.fr / DINUM).
 *
 * - Aucune authentification
 * - Limite officielle : 7 req/s (throttle interne 150 ms par défaut)
 * - Pagination : `page` + `per_page` (25 max recommandé pour rester sous le timeout)
 *
 * @see https://recherche-entreprises.api.gouv.fr/docs
 */
final class RechercheEntreprisesClient extends AbstractGouvApiClient
{
    public function __construct()
    {
        parent::__construct(
            baseUrl: (string) config('prospects.api.recherche_entreprises'),
            timeout: 20,
            throttleMicroseconds: (int) config('prospects.throttle_us.recherche_entreprises', 150_000),
        );
    }

    /**
     * Recherche une page de résultats. Retourne le payload JSON brut tel que renvoyé par l'API.
     *
     * @param  array<string, scalar|null>  $filters  ex. ['code_postal' => '33000', 'activite_principale' => '46.69B']
     * @return array{results: list<array<string, mixed>>, total_results: int, page: int, per_page: int, total_pages: int}
     */
    public function search(array $filters, int $page = 1, int $perPage = 25): array
    {
        $this->throttle();

        $params = array_merge([
            'etat_administratif' => 'A',
            'per_page' => $perPage,
            'page' => $page,
        ], array_filter($filters, static fn ($v) => $v !== null && $v !== ''));

        try {
            $response = $this->http()->get('/search', $params)->throw();
        } catch (RequestException $e) {
            $this->logFailure('search', $e, ['filters' => $params]);
            throw $e;
        }

        $data = $response->json();

        return [
            'results' => $data['results'] ?? [],
            'total_results' => (int) ($data['total_results'] ?? 0),
            'page' => (int) ($data['page'] ?? $page),
            'per_page' => (int) ($data['per_page'] ?? $perPage),
            'total_pages' => (int) ($data['total_pages'] ?? 1),
        ];
    }

    /**
     * Itère sur toutes les pages jusqu'à `maxPages` (1-indexed inclus).
     *
     * Yields chaque entreprise individuellement → permet à l'orchestrateur
     * d'avancer la progress bar par item.
     *
     * @param  array<string, scalar|null>  $filters
     * @return Generator<int, array<string, mixed>, void, void>
     */
    public function iterate(array $filters, int $maxPages = 10, int $perPage = 25): Generator
    {
        for ($page = 1; $page <= $maxPages; $page++) {
            $result = $this->search($filters, $page, $perPage);

            foreach ($result['results'] as $entreprise) {
                yield $entreprise;
            }

            if ($page >= $result['total_pages']) {
                break;
            }
        }
    }

    /**
     * Compte total estimé d'une requête (sans tout charger).
     *
     * @param  array<string, scalar|null>  $filters
     */
    public function count(array $filters): int
    {
        $result = $this->search($filters, page: 1, perPage: 1);

        return $result['total_results'];
    }
}
