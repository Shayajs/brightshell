<?php

declare(strict_types=1);

namespace App\Services\Prospects;

use App\Services\Prospects\Scoring\Dto\BodaccEvent;
use App\Services\Prospects\Scoring\Dto\BodaccEventType;
use Illuminate\Http\Client\RequestException;

/**
 * Client du BODACC (Bulletin Officiel des Annonces Civiles et Commerciales)
 * exposé via OpenDataSoft (DILA — opendatasoft.com).
 *
 * Aucune authentification ; jeu de données `annonces-commerciales`.
 *
 * @see https://bodacc-datadila.opendatasoft.com/explore/dataset/annonces-commerciales/api/
 */
final class BodaccClient extends AbstractGouvApiClient
{
    public function __construct()
    {
        parent::__construct(
            baseUrl: 'https://bodacc-datadila.opendatasoft.com',
            timeout: 15,
            throttleMicroseconds: (int) config('prospects.throttle_us.bodacc', 300_000),
        );
    }

    /**
     * Récupère les actes BODACC d'un SIREN, normalisés en {@see BodaccEvent}.
     *
     * @return list<BodaccEvent>
     */
    public function forSiren(string $siren, int $limit = 25): array
    {
        $this->throttle();

        try {
            $response = $this->http()
                ->get('/api/records/1.0/search/', [
                    'dataset' => 'annonces-commerciales',
                    'q' => sprintf('registre:"%s"', $siren),
                    'rows' => $limit,
                    'sort' => '-dateparution',
                ])
                ->throw();
        } catch (RequestException $e) {
            $this->logFailure('forSiren', $e, ['siren' => $siren]);

            return [];
        }

        $records = $response->json('records') ?? [];
        $events = [];

        foreach ($records as $record) {
            $fields = $record['fields'] ?? [];

            $rawDate = $fields['dateparution'] ?? null;
            if (! is_string($rawDate)) {
                continue;
            }

            try {
                $date = new \DateTimeImmutable($rawDate);
            } catch (\Throwable) {
                continue;
            }

            // Le BODACC concatène plusieurs blocs textuels (modificationsGenerales, depot, etc.).
            $libelle = self::extractLibelle($fields);
            $type = BodaccEventType::detectFromLibelle($libelle);

            $events[] = new BodaccEvent(
                type: $type,
                date: $date,
                libelle: $libelle,
                payload: $fields,
            );
        }

        return $events;
    }

    private static function extractLibelle(array $fields): string
    {
        $candidates = [
            $fields['modificationsgenerales'] ?? null,
            $fields['depot'] ?? null,
            $fields['ventes'] ?? null,
            $fields['acte'] ?? null,
            $fields['typeavis_lib'] ?? null,
            $fields['familleavis_lib'] ?? null,
            $fields['listepersonnes'] ?? null,
        ];

        $parts = [];
        foreach ($candidates as $value) {
            if (is_string($value) && $value !== '') {
                $parts[] = $value;
            } elseif (is_array($value)) {
                $parts[] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }

        return implode(' · ', $parts) ?: 'Acte BODACC';
    }
}
