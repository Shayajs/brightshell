<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring;

use App\Services\Prospects\Geo\HaversineDistance;
use App\Services\Prospects\Scoring\Dto\BodaccEvent;
use App\Services\Prospects\Scoring\Dto\BodaccEventType;
use App\Services\Prospects\Scoring\Dto\ProspectInput;
use Carbon\CarbonImmutable;

/**
 * Couche B du scoring : détecte les patterns croisés et retourne les modificateurs
 * à appliquer (multiplicateurs ou bonus secs), accompagnés d'une justification FR.
 *
 * Structure d'un modificateur :
 *   [
 *     'multiplier' => 1.5,             // appliqué multiplicativement
 *     'flat_bonus' => 0,               // bonus sec ajouté APRÈS multiplicateur
 *     'targets' => ['global'|'website'|'software'|'*'],
 *     'why' => 'phrase courte FR pour l’UI',
 *   ]
 */
final class NonLinearModifiers
{
    public function __construct(
        private readonly NafCategorizer $naf,
    ) {}

    /**
     * @return array<string, array{multiplier: float, flat_bonus: int, targets: list<string>, why: string}>
     */
    public function detect(ProspectInput $in): array
    {
        $mods = [];

        // ─── Véto NAF : hors-cible total (SCI, holding, agriculture, détail…) ──
        if ($this->naf->categorize($in->codeNaf) === NafCategorizer::EXCLUDED) {
            $mods['veto.naf_exclu'] = [
                'multiplier' => 0.0,
                'flat_bonus' => 0,
                'targets' => ['*'],
                'why' => 'Secteur hors-cible (NAF exclu : holdings, SCI, agriculture, commerce de détail, etc.).',
            ];

            return $mods;
        }

        // ─── Véto BODACC : procédure collective active ───────────────────────
        if ($in->hasProcedureCollective()) {
            $mods['veto.procedure_collective'] = [
                'multiplier' => 0.0,
                'flat_bonus' => 0,
                'targets' => ['*'],
                'why' => 'Procédure collective active dans le BODACC (RJ, sauvegarde ou liquidation).',
            ];

            return $mods;
        }

        // ─── Relais générationnel (×1.5) ─────────────────────────────────────
        if ($this->isRelaisGenerationnel($in)) {
            $cfg = (array) config('prospects.modifiers.relais_generationnel', []);
            $mods['relais_generationnel'] = [
                'multiplier' => (float) ($cfg['multiplier'] ?? 1.5),
                'flat_bonus' => 0,
                'targets' => ['*'],
                'why' => 'Reprise récente d’une entreprise installée : refonte digitale très probable.',
            ];
        }

        // ─── Zéro salarié industriel (×1.2) ──────────────────────────────────
        if ($this->isZeroSalarieIndustriel($in)) {
            $cfg = (array) config('prospects.modifiers.zero_salarie_industriel', []);
            $mods['zero_salarie_industriel'] = [
                'multiplier' => (float) ($cfg['multiplier'] ?? 1.2),
                'flat_bonus' => 0,
                'targets' => ['website', 'global'],
                'why' => 'Restructuration en cours malgré 0 salarié : besoin de clarifier la nouvelle offre via le web.',
            ];
        }

        // ─── Usine à gaz humaine (×1.3, software uniquement) ─────────────────
        if ($this->isUsineAGazHumaine($in)) {
            $cfg = (array) config('prospects.modifiers.usine_a_gaz_humaine', []);
            $mods['usine_a_gaz_humaine'] = [
                'multiplier' => (float) ($cfg['multiplier'] ?? 1.3),
                'flat_bonus' => 0,
                'targets' => ['software'],
                'why' => 'Effectif élevé + CA stagnant en B2B : besoin d’outils internes pour retrouver de la marge.',
            ];
        }

        // ─── Momentum de signaux (×1.15) ─────────────────────────────────────
        if ($this->hasMomentumSignaux($in)) {
            $cfg = (array) config('prospects.modifiers.momentum_signaux', []);
            $mods['momentum_signaux'] = [
                'multiplier' => (float) ($cfg['multiplier'] ?? 1.15),
                'flat_bonus' => 0,
                'targets' => ['*'],
                'why' => 'Plusieurs actes BODACC distincts en moins de 6 mois : entreprise en pleine mutation.',
            ];
        }

        // NOTE : la détection « digital_gap » est remplacée par les détecteurs
        // de besoins (`prospects.needs.*`) qui fonctionnent en signaux indépendants
        // — voir App\Services\Prospects\Needs\Detectors\*.

        // ─── Hub local (bonus +5 pts) ────────────────────────────────────────
        if ($in->nombreEtablissements >= (int) config('prospects.modifiers.hub_local.min_etablissements', 3)) {
            $cfg = (array) config('prospects.modifiers.hub_local', []);
            $mods['hub_local'] = [
                'multiplier' => 1.0,
                'flat_bonus' => (int) ($cfg['bonus_points'] ?? 5),
                'targets' => ['*'],
                'why' => "Plusieurs établissements : prescripteur potentiel à l'échelle locale.",
            ];
        }

        // ─── Proximité géographique (bonus pondéré jusqu’à +5 pts) ───────────
        $proximityBonus = $this->proximiteGeographique($in);
        if ($proximityBonus > 0) {
            $mods['proximite_geographique'] = [
                'multiplier' => 1.0,
                'flat_bonus' => $proximityBonus,
                'targets' => ['*'],
                'why' => "Prospect à proximité immédiate de la base (≤ rayon configuré).",
            ];
        }

        return $mods;
    }

    // ─── Détection des patterns ─────────────────────────────────────────────

    private function isRelaisGenerationnel(ProspectInput $in): bool
    {
        $cfg = (array) config('prospects.modifiers.relais_generationnel', []);
        $minAgeEntreprise = (int) ($cfg['min_age_entreprise'] ?? 20);
        $maxAgeDirigeant = (int) ($cfg['max_age_dirigeant'] ?? 40);
        $fenetreMois = (int) ($cfg['fenetre_nomination_mois'] ?? 12);

        $ageEntreprise = $in->ageEntrepriseAnnees();
        $ageDirigeant = $in->ageDirigeantAnnees();
        if ($ageEntreprise === null || $ageEntreprise <= $minAgeEntreprise) {
            return false;
        }
        if ($ageDirigeant === null || $ageDirigeant >= $maxAgeDirigeant) {
            return false;
        }

        // Nomination récente : soit date explicite, soit événement BODACC.
        if ($in->dateNominationDirigeant !== null) {
            $months = (float) $in->dateNominationDirigeant->diffInMonths(CarbonImmutable::now());
            if ($months <= $fenetreMois) {
                return true;
            }
        }

        foreach ($in->bodaccEvents as $event) {
            if ($event->type === BodaccEventType::ChangementDirigeant && $event->ageInMonths() <= $fenetreMois) {
                return true;
            }
        }

        return false;
    }

    private function isZeroSalarieIndustriel(ProspectInput $in): bool
    {
        $cfg = (array) config('prospects.modifiers.zero_salarie_industriel', []);
        $fenetreMois = (int) ($cfg['fenetre_acte_mois'] ?? 12);

        $isZero = in_array($in->trancheEffectif, (array) config('prospects.scoring.effectif.zero', []), true);
        if (! $isZero) {
            return false;
        }
        if (! $this->naf->isIndustrielOuNegoce($in->codeNaf)) {
            return false;
        }

        foreach ($in->bodaccEvents as $event) {
            if (in_array($event->type, [BodaccEventType::Fusion, BodaccEventType::Creation], true)
                && $event->ageInMonths() <= $fenetreMois) {
                return true;
            }
        }

        return false;
    }

    private function isUsineAGazHumaine(ProspectInput $in): bool
    {
        $cfg = (array) config('prospects.modifiers.usine_a_gaz_humaine', []);
        $minEffectif = (int) ($cfg['min_effectif'] ?? 20);

        // Approximation effectif via tranche INSEE.
        $effectifEstime = $this->effectifEstime($in->trancheEffectif);
        if ($effectifEstime === null || $effectifEstime < $minEffectif) {
            return false;
        }

        $variation = $in->variationCa();
        if ($variation === null) {
            return false;
        }
        $min = (float) ($cfg['ca_variation_min'] ?? -0.05);
        $max = (float) ($cfg['ca_variation_max'] ?? 0.02);
        if ($variation < $min || $variation > $max) {
            return false;
        }

        return $this->naf->isB2B($in->codeNaf);
    }

    private function hasMomentumSignaux(ProspectInput $in): bool
    {
        $cfg = (array) config('prospects.modifiers.momentum_signaux', []);
        $min = (int) ($cfg['min_evenements'] ?? 2);
        $fenetreMois = (int) ($cfg['fenetre_mois'] ?? 6);

        $count = 0;
        $seenTypes = [];
        foreach ($in->bodaccEvents as $event) {
            if ($event->ageInMonths() > $fenetreMois) {
                continue;
            }
            // On veut des types DISTINCTS pour éviter de compter 3x le même dépôt.
            $key = $event->type->value;
            if (in_array($key, $seenTypes, true)) {
                continue;
            }
            $seenTypes[] = $key;
            $count++;
            if ($count >= $min) {
                return true;
            }
        }

        return false;
    }

    private function proximiteGeographique(ProspectInput $in): int
    {
        $homeLat = config('prospects.home.lat');
        $homeLong = config('prospects.home.long');
        $radius = (int) config('prospects.home.radius_km', 30);

        if ($homeLat === null || $homeLong === null || $in->latitude === null || $in->longitude === null) {
            return 0;
        }

        $distance = HaversineDistance::kilometers(
            (float) $homeLat,
            (float) $homeLong,
            $in->latitude,
            $in->longitude
        );

        if ($distance > $radius || $radius <= 0) {
            return 0;
        }

        $bonusMax = (int) config('prospects.modifiers.proximite_geographique.bonus_max', 5);
        // Bonus linéaire : plein bonus à 0 km, zéro au rayon limite.
        $ratio = 1 - ($distance / $radius);

        return (int) round($bonusMax * $ratio);
    }

    /**
     * Estimation centre de fourchette pour la tranche d'effectif INSEE.
     */
    private function effectifEstime(?string $tranche): ?int
    {
        return match ($tranche) {
            '00' => 0,
            '01' => 1,
            '02' => 4,
            '03' => 7,
            '11' => 15,
            '12' => 35,
            '21' => 75,
            '22' => 150,
            '31' => 200,
            '32' => 300,
            '41' => 500,
            '42' => 1500,
            '51' => 2500,
            '52' => 7500,
            '53' => 15000,
            default => null,
        };
    }
}
