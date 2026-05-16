<?php

declare(strict_types=1);

namespace App\Actions\Prospects;

use App\Models\Prospect;
use App\Services\Prospects\ApiAdresseClient;
use App\Services\Prospects\BodaccClient;
use App\Services\Prospects\ClearbitLogoResolver;
use App\Services\Prospects\Geo\HaversineDistance;
use App\Services\Prospects\InpiPisteClient;
use App\Services\Prospects\RechercheEntreprisesClient;
use App\Services\Prospects\Scoring\Dto\BodaccEvent;
use App\Services\Prospects\Scoring\Dto\ProspectInput;
use App\Services\Prospects\Scoring\ScoreBand;
use App\Services\Prospects\Scoring\ScoreEngine;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Orchestrateur d'import.
 *
 * Étapes par entreprise :
 *   1. Hydrate {@see ProspectInput} depuis Recherche Entreprises
 *   2. Géocode (BAN, caché) → lat/long + distance Haversine
 *   3. Récupère les actes BODACC (signaux faibles + véto)
 *   4. Score via {@see ScoreEngine}
 *   5. Si band ≥ priority ET INPI activé → enrichit le bilan
 *   6. Upsert en BDD (`siren` unique) + ligne CSV (sauf `excluded` filtré par min_band)
 */
final class ImportProspectsAction
{
    public function __construct(
        private readonly RechercheEntreprisesClient $api,
        private readonly BodaccClient $bodacc,
        private readonly ApiAdresseClient $banClient,
        private readonly InpiPisteClient $inpi,
        private readonly ScoreEngine $engine,
        private readonly ClearbitLogoResolver $logoResolver,
    ) {}

    /**
     * @param  Closure(int $current, int $total, string $step): void|null  $onProgress
     */
    public function run(ImportOptions $options, ?Closure $onProgress = null): ImportResult
    {
        $start = microtime(true);
        $filters = $options->toApiFilters();

        // Total connu en 1 ping (page 1, per_page 1).
        try {
            $total = $this->api->count($filters);
        } catch (\Throwable $e) {
            Log::error('[Prospects][Import] count failed', ['error' => $e->getMessage(), 'filters' => $filters]);
            $total = 0;
        }
        $expected = min($total, $options->maxPages * $options->perPage);

        $csvHandle = null;
        $csvPath = null;
        if ($options->withCsv) {
            [$csvHandle, $csvPath] = $this->openCsv($options);
        }

        $stats = [
            'fetched' => 0,
            'kept' => 0,
            'excluded' => 0,
            'by_band' => [],
            'by_niveau' => [],
            'by_modifier' => [],
        ];

        $minBandOrder = (ScoreBand::tryFrom($options->minBand) ?? ScoreBand::Watch)->sortOrder();

        try {
            foreach ($this->api->iterate($filters, $options->maxPages, $options->perPage) as $entreprise) {
                $stats['fetched']++;
                if ($onProgress !== null) {
                    $onProgress($stats['fetched'], $expected, 'fetching');
                }

                try {
                    $prospect = $this->processOne($entreprise, $options);
                } catch (\Throwable $e) {
                    Log::warning('[Prospects][Import] item failed', [
                        'siren' => $entreprise['siren'] ?? null,
                        'error' => $e->getMessage(),
                    ]);

                    continue;
                }

                $band = $prospect->band();
                $stats['by_band'][$band->value] = ($stats['by_band'][$band->value] ?? 0) + 1;
                $stats['by_niveau'][$prospect->niveau_interet] = ($stats['by_niveau'][$prospect->niveau_interet] ?? 0) + 1;

                foreach ((array) ($prospect->score_breakdown['modifiers'] ?? []) as $modKey => $_) {
                    $stats['by_modifier'][$modKey] = ($stats['by_modifier'][$modKey] ?? 0) + 1;
                }

                if ($band === ScoreBand::Excluded) {
                    $stats['excluded']++;

                    continue;
                }
                $stats['kept']++;

                // CSV : on respecte le seuil minBand demandé par l'utilisateur.
                if ($csvHandle !== null && $band->sortOrder() <= $minBandOrder) {
                    $this->writeCsvRow($csvHandle, $prospect);
                }
            }
        } finally {
            if ($csvHandle !== null) {
                fclose($csvHandle);
            }
        }

        return new ImportResult(
            fetched: $stats['fetched'],
            kept: $stats['kept'],
            excluded: $stats['excluded'],
            byBand: $stats['by_band'],
            byNiveau: $stats['by_niveau'],
            byModifier: $stats['by_modifier'],
            csvPath: $csvPath,
            durationMs: (int) round((microtime(true) - $start) * 1000),
        );
    }

    /**
     * Traite une entreprise individuelle (enrichissement + scoring + persistance).
     */
    private function processOne(array $entreprise, ImportOptions $options): Prospect
    {
        $siren = (string) ($entreprise['siren'] ?? '');
        if ($siren === '') {
            throw new \RuntimeException('SIREN manquant');
        }

        $dirigeant = $entreprise['dirigeants'][0] ?? null;
        $siege = $entreprise['siege'] ?? [];

        $dateCreation = $this->parseDate($entreprise['date_creation'] ?? null);
        $dateNaissanceDirigeant = $this->parseDate(
            isset($dirigeant['date_naissance']) ? $dirigeant['date_naissance'].'-01' : null,
            'Y-m-d'
        );

        $emailContact = $siege['email'] ?? $entreprise['email'] ?? null;
        $siteInternet = $siege['site_internet'] ?? $entreprise['site_internet'] ?? null;

        // ─── Géocodage (caché 30j) ───────────────────────────────────────────
        $lat = isset($siege['latitude']) ? (float) $siege['latitude'] : null;
        $long = isset($siege['longitude']) ? (float) $siege['longitude'] : null;
        $codeInseeCommune = $siege['code_commune'] ?? null;

        if ($options->withGeocoding && ($lat === null || $long === null)) {
            $query = trim(implode(' ', array_filter([
                $siege['adresse'] ?? null,
                $siege['code_postal'] ?? null,
                $siege['libelle_commune'] ?? null,
            ])));
            if ($query !== '') {
                $geo = $this->banClient->geocode($query, $siege['code_postal'] ?? null);
                if ($geo !== null) {
                    $lat = $geo->latitude;
                    $long = $geo->longitude;
                    $codeInseeCommune ??= $geo->codeInseeCommune;
                }
            }
        }

        // Distance home (config) — utilisée par le modificateur proximite_geographique.
        $distanceKm = $this->computeDistanceFromHome($lat, $long);

        // ─── BODACC ──────────────────────────────────────────────────────────
        $bodaccEvents = [];
        $bodaccConsulted = false;
        if ($options->withBodacc) {
            try {
                $bodaccEvents = $this->bodacc->forSiren($siren);
                $bodaccConsulted = true;
            } catch (\Throwable $e) {
                Log::info('[Prospects][BODACC] skipped', ['siren' => $siren, 'error' => $e->getMessage()]);
            }
        }

        // ─── Scoring ─────────────────────────────────────────────────────────
        $input = new ProspectInput(
            siren: $siren,
            nomEntreprise: (string) ($entreprise['nom_complet'] ?? $entreprise['nom_raison_sociale'] ?? 'Entreprise inconnue'),
            codeNaf: $entreprise['activite_principale'] ?? null,
            natureJuridique: $entreprise['nature_juridique'] ?? null,
            trancheEffectif: $entreprise['tranche_effectif_salarie'] ?? null,
            dateCreation: $dateCreation,
            dateNaissanceDirigeant: $dateNaissanceDirigeant,
            dateNominationDirigeant: $this->parseDate($dirigeant['date_nomination'] ?? null),
            siteInternet: is_string($siteInternet) ? $siteInternet : null,
            emailContact: is_string($emailContact) ? $emailContact : null,
            nombreEtablissements: (int) ($entreprise['nombre_etablissements'] ?? $entreprise['nombre_etablissements_ouverts'] ?? 1),
            latitude: $lat,
            longitude: $long,
            distanceKmHome: $distanceKm,
            bodaccEvents: $bodaccEvents,
            bodaccConsulted: $bodaccConsulted,
            financesAvailable: false,
        );

        $result = $this->engine->compute($input);

        // ─── Enrichissement INPI ciblé (Hot/Priority seulement) ──────────────
        $chiffreAffaires = null;
        $chiffreAffairesNm1 = null;
        $resultatNet = null;
        $exerciceBilan = null;
        if ($options->withInpi
            && $this->inpi->isEnabled()
            && in_array($result->band, [ScoreBand::Hot, ScoreBand::Priority], true)) {
            $bilan = $this->inpi->dernierBilan($siren);
            if ($bilan !== null) {
                $chiffreAffaires = $bilan['chiffre_affaires'] ?? null;
                $chiffreAffairesNm1 = $bilan['chiffre_affaires_n_moins_1'] ?? null;
                $resultatNet = $bilan['resultat_net'] ?? null;
                $exerciceBilan = $bilan['exercice'] ?? null;

                if ($chiffreAffaires !== null) {
                    // Re-scoring avec finances : peut faire basculer en usine_a_gaz_humaine + boost confidence.
                    $enriched = $input->withFinances($chiffreAffaires, $chiffreAffairesNm1, $resultatNet);
                    try {
                        $result = $this->engine->compute($enriched);
                    } catch (\Throwable) {
                        // garde l'ancien résultat
                    }
                }
            }
        }

        // ─── Logo Clearbit (domaine déduit) ──────────────────────────────────
        $domain = $this->logoResolver->resolveDomain(
            is_string($siteInternet) ? $siteInternet : null,
            is_string($emailContact) ? $emailContact : null,
        );
        $logoUrl = $this->logoResolver->logoUrl($domain);

        // ─── Persistance ─────────────────────────────────────────────────────
        return Prospect::query()->updateOrCreate(
            ['siren' => $siren],
            [
                'siret' => $siege['siret'] ?? null,
                'nom_entreprise' => $input->nomEntreprise,
                'nom_dirigeant' => $dirigeant['nom'] ?? null,
                'prenom_dirigeant' => $dirigeant['prenoms'] ?? ($dirigeant['prenom'] ?? null),
                'date_naissance_dirigeant' => $dateNaissanceDirigeant?->toDateString(),
                'date_nomination_dirigeant' => $input->dateNominationDirigeant?->toDateString(),
                'code_naf' => $input->codeNaf,
                'libelle_naf' => $entreprise['libelle_activite_principale'] ?? null,
                'nature_juridique' => $input->natureJuridique,
                'tranche_effectif' => $input->trancheEffectif,
                'nombre_etablissements' => $input->nombreEtablissements,
                'site_internet' => $input->siteInternet,
                'email_contact' => $input->emailContact,
                'telephone' => $siege['telephone'] ?? null,
                'adresse' => $siege['adresse'] ?? null,
                'code_postal' => $siege['code_postal'] ?? null,
                'ville' => $siege['libelle_commune'] ?? null,
                'departement' => self::extractDepartement($siege['code_postal'] ?? null),
                'region' => $siege['region'] ?? null,
                'code_insee_commune' => $codeInseeCommune,
                'latitude' => $lat,
                'longitude' => $long,
                'distance_km_home' => $distanceKm,
                'date_creation' => $dateCreation?->toDateString(),
                'domaine_web' => $domain,
                'logo_url' => $logoUrl,
                'chiffre_affaires' => $chiffreAffaires,
                'chiffre_affaires_n_moins_1' => $chiffreAffairesNm1,
                'resultat_net' => $resultatNet,
                'exercice_bilan' => $exerciceBilan,
                'score_global' => $result->scoreGlobal,
                'score_website' => $result->scoreWebsite,
                'score_software' => $result->scoreSoftware,
                'score_band' => $result->band->value,
                'niveau_interet' => $result->niveauLegacy,
                'score_breakdown' => $result->breakdown,
                'score_confidence' => $result->confidence,
                'raw_payload' => $entreprise,
                'bodacc_events' => array_map(static fn (BodaccEvent $e) => $e->toArray(), $bodaccEvents),
                'procedure_collective' => $input->hasProcedureCollective(),
                'scored_at' => now(),
            ]
        );
    }

    private function computeDistanceFromHome(?float $lat, ?float $long): ?int
    {
        $homeLat = config('prospects.home.lat');
        $homeLong = config('prospects.home.long');
        if ($lat === null || $long === null || $homeLat === null || $homeLong === null) {
            return null;
        }

        return (int) round(HaversineDistance::kilometers((float) $homeLat, (float) $homeLong, $lat, $long));
    }

    private function parseDate(?string $value, string $format = 'Y-m-d'): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return CarbonImmutable::createFromFormat('!'.$format, $value) ?: CarbonImmutable::parse($value);
        } catch (\Throwable) {
            try {
                return CarbonImmutable::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }
    }

    private static function extractDepartement(?string $codePostal): ?string
    {
        if ($codePostal === null || strlen($codePostal) < 2) {
            return null;
        }
        $first2 = substr($codePostal, 0, 2);

        // Corse : 2A/2B → on garde le code postal le plus parlant (20*).
        if ($first2 === '20') {
            return '20';
        }

        // DOM-TOM : 97x / 98x → 3 chiffres.
        if ($first2 === '97' || $first2 === '98') {
            return substr($codePostal, 0, 3);
        }

        return $first2;
    }

    /**
     * @return array{0: resource, 1: string}
     */
    private function openCsv(ImportOptions $options): array
    {
        $directory = (string) config('prospects.csv_directory', 'prospects');
        $disk = (string) config('prospects.csv_disk', 'local');
        Storage::disk($disk)->makeDirectory($directory);

        $filename = sprintf('%s/prospects_%s_%s.csv', $directory, $options->zoneLabel(), now()->format('Y-m-d_His'));
        $absolutePath = Storage::disk($disk)->path($filename);

        $handle = fopen($absolutePath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException("Impossible d'ouvrir le fichier CSV : {$absolutePath}");
        }
        // BOM UTF-8 pour Excel.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'SIREN', 'Nom_Entreprise', 'Nom_Dirigeant', 'Code_NAF', 'Adresse_Complete',
            'Ville', 'Tranche_Effectif', 'Niveau_Interet',
            'Score_Global', 'Score_Website', 'Score_Software', 'Band',
            'Multiplicateurs_Declenches', 'Confidence', 'Distance_Home_Km',
        ], escape: '\\');

        return [$handle, $absolutePath];
    }

    /**
     * @param  resource  $handle
     */
    private function writeCsvRow($handle, Prospect $p): void
    {
        $modifiers = array_keys((array) ($p->score_breakdown['modifiers'] ?? []));

        fputcsv($handle, [
            $p->siren,
            $p->nom_entreprise,
            trim(($p->prenom_dirigeant ?? '').' '.($p->nom_dirigeant ?? '')),
            $p->code_naf,
            trim(($p->adresse ?? '').' '.($p->code_postal ?? '').' '.($p->ville ?? '')),
            $p->ville,
            $p->tranche_effectif,
            $p->niveau_interet,
            $p->score_global,
            $p->score_website,
            $p->score_software,
            $p->score_band,
            implode('|', $modifiers),
            $p->score_confidence,
            $p->distance_km_home,
        ], escape: '\\');
    }
}
