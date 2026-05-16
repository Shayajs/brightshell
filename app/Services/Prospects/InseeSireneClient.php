<?php

declare(strict_types=1);

namespace App\Services\Prospects;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Client INSEE Sirene V3 (optionnel — actif si INSEE_TOKEN défini).
 *
 * - Auth : Bearer token (compte gratuit sur https://api.insee.fr)
 * - Limite : 30 req/min (extensible sur demande)
 * - Permet des requêtes Lucene avancées : `q=activitePrincipaleUniteLegale:46.69B AND dateCreationUniteLegale:[2010-01-01 TO 2020-12-31]`
 *
 * @see https://api.insee.fr/catalogue/site/themes/wso2/subthemes/insee/pages/item-info.jag?name=Sirene&version=V3&provider=insee
 */
final class InseeSireneClient extends AbstractGouvApiClient
{
    public function __construct(
        private readonly ?string $token = null,
    ) {
        parent::__construct(
            baseUrl: 'https://api.insee.fr/entreprises/sirene/V3',
            timeout: 20,
            throttleMicroseconds: (int) config('prospects.throttle_us.insee', 2_100_000),
        );
    }

    public function isEnabled(): bool
    {
        return $this->token !== null && $this->token !== '';
    }

    protected function http(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->withUserAgent('BrightShell-Prospects/1.0')
            ->withToken((string) $this->token)
            ->retry(2, 500, function (\Throwable $e): bool {
                return $e instanceof RequestException
                    && $e->response !== null
                    && in_array($e->response->status(), [429, 500, 502, 503, 504], true);
            });

        return $request;
    }

    /**
     * Recherche d'unités légales via syntaxe Apache Solr.
     *
     * @return list<array<string, mixed>>
     */
    public function searchLucene(string $query, int $limit = 100): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $this->throttle();

        try {
            $response = $this->http()->get('/siret', [
                'q' => $query,
                'nombre' => $limit,
            ])->throw();
        } catch (RequestException $e) {
            $this->logFailure('searchLucene', $e, ['q' => $query]);

            return [];
        }

        return $response->json('etablissements') ?? [];
    }
}
