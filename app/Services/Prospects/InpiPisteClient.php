<?php

declare(strict_types=1);

namespace App\Services\Prospects;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Client INPI PISTE — Registre National des Entreprises (optionnel).
 *
 * Activé seulement si INPI_TOKEN défini ; utilisé pour les prospects band ≥ priority
 * afin de récupérer les bilans complets (CA, résultat net, capitaux propres).
 *
 * @see https://piste.inpi.fr/
 */
final class InpiPisteClient extends AbstractGouvApiClient
{
    public function __construct(
        private readonly ?string $token = null,
    ) {
        parent::__construct(
            baseUrl: 'https://registre-national-entreprises.inpi.fr/api',
            timeout: 25,
            throttleMicroseconds: (int) config('prospects.throttle_us.inpi', 800_000),
        );
    }

    public function isEnabled(): bool
    {
        return $this->token !== null && $this->token !== '';
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->withUserAgent('BrightShell-Prospects/1.0')
            ->withToken((string) $this->token)
            ->retry(2, 400, function (\Throwable $e): bool {
                return $e instanceof RequestException
                    && $e->response !== null
                    && in_array($e->response->status(), [429, 500, 502, 503, 504], true);
            });
    }

    /**
     * Retourne le dernier bilan disponible pour un SIREN (CA, résultat net, exercice).
     *
     * @return array{chiffre_affaires?: int, chiffre_affaires_n_moins_1?: int, resultat_net?: int, exercice?: int}|null
     */
    public function dernierBilan(string $siren): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $this->throttle();

        try {
            $response = $this->http()->get("/companies/{$siren}")->throw();
        } catch (RequestException $e) {
            $this->logFailure('dernierBilan', $e, ['siren' => $siren]);

            return null;
        }

        $data = $response->json() ?? [];
        // Structure exacte dépend de la version PISTE — on extrait les clés usuelles avec fallback.
        $bilans = $data['comptesAnnuels'] ?? $data['bilans'] ?? [];
        if ($bilans === []) {
            return null;
        }

        // Tri décroissant par exercice.
        usort($bilans, static fn (array $a, array $b): int => (int) ($b['exercice'] ?? 0) <=> (int) ($a['exercice'] ?? 0));
        $last = $bilans[0] ?? [];
        $prev = $bilans[1] ?? [];

        $ca = self::pick($last, ['chiffreAffaires', 'ca', 'chiffreAffairesNet']);
        $caPrev = self::pick($prev, ['chiffreAffaires', 'ca', 'chiffreAffairesNet']);
        $rn = self::pick($last, ['resultatNet', 'resultat']);
        $exercice = self::pick($last, ['exercice', 'annee']);

        return array_filter([
            'chiffre_affaires' => $ca !== null ? (int) $ca : null,
            'chiffre_affaires_n_moins_1' => $caPrev !== null ? (int) $caPrev : null,
            'resultat_net' => $rn !== null ? (int) $rn : null,
            'exercice' => $exercice !== null ? (int) $exercice : null,
        ], static fn ($v) => $v !== null);
    }

    private static function pick(array $arr, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $arr) && $arr[$key] !== null) {
                return $arr[$key];
            }
        }

        return null;
    }
}
