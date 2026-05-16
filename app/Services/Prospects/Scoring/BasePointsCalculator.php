<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring;

use App\Services\Prospects\Scoring\Dto\BodaccEventType;
use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Couche A du scoring : calcule les points BRUTS (avant multiplicateurs).
 *
 * Sortie : tableau `['naf', 'structure', 'gouvernance', 'signaux', 'confidence']`.
 * Score brut max : 100 (NAF 30 + Structure 30 + Gouvernance 15 + Signaux 25).
 */
final class BasePointsCalculator
{
    public function __construct(
        private readonly NafCategorizer $naf,
    ) {}

    /**
     * @return array{naf: int, structure: int, gouvernance: int, signaux: int, confidence: int, details: array<string, mixed>}
     */
    public function compute(ProspectInput $in): array
    {
        $naf = $this->naf->points($in->codeNaf);
        $structure = $this->pointsStructure($in);
        $gouvernance = $this->pointsGouvernance($in);
        ['signaux' => $signaux, 'detail' => $signauxDetail] = $this->pointsSignaux($in);
        $confidence = $this->confidence($in);

        return [
            'naf' => $naf,
            'structure' => $structure,
            'gouvernance' => $gouvernance,
            'signaux' => $signaux,
            'confidence' => $confidence,
            'details' => [
                'naf_category' => $this->naf->categorize($in->codeNaf),
                'effectif_points' => $this->pointsEffectif($in),
                'age_points' => $this->pointsAge($in),
                'signaux_breakdown' => $signauxDetail,
            ],
        ];
    }

    private function pointsStructure(ProspectInput $in): int
    {
        return min(30, $this->pointsEffectif($in) + $this->pointsAge($in));
    }

    private function pointsEffectif(ProspectInput $in): int
    {
        $code = $in->trancheEffectif;
        if ($code === null || $code === '') {
            return (int) config('prospects.scoring.effectif.zero_points', 0);
        }

        if (in_array($code, (array) config('prospects.scoring.effectif.zero', []), true)) {
            return (int) config('prospects.scoring.effectif.zero_points', 0);
        }
        if (in_array($code, (array) config('prospects.scoring.effectif.sweet_spot', []), true)) {
            return (int) config('prospects.scoring.effectif.sweet_spot_points', 15);
        }
        if (in_array($code, (array) config('prospects.scoring.effectif.small', []), true)) {
            return (int) config('prospects.scoring.effectif.small_points', 10);
        }
        if (in_array($code, (array) config('prospects.scoring.effectif.large', []), true)) {
            return (int) config('prospects.scoring.effectif.large_points', 12);
        }

        return 5;
    }

    private function pointsAge(ProspectInput $in): int
    {
        $annees = $in->ageEntrepriseAnnees();
        if ($annees === null) {
            return 0;
        }

        return $this->matchBand((array) config('prospects.scoring.age_entreprise.bands', []), $annees);
    }

    private function pointsGouvernance(ProspectInput $in): int
    {
        $annees = $in->ageDirigeantAnnees();
        if ($annees === null) {
            return 0;
        }

        return $this->matchBand((array) config('prospects.scoring.gouvernance.bands', []), $annees);
    }

    /**
     * @return array{signaux: int, detail: array<string, float>}
     */
    private function pointsSignaux(ProspectInput $in): array
    {
        $decay = (float) config('prospects.scoring.signaux.decay_months', 12);
        $cap = (int) config('prospects.scoring.signaux.cap', 25);
        $weights = (array) config('prospects.scoring.signaux.points', []);

        // Mapping type → poids
        $typeWeight = [
            BodaccEventType::Demenagement->value => (float) ($weights['demenagement'] ?? 0),
            BodaccEventType::AugmentationCapital->value => (float) ($weights['augmentation_capital'] ?? 0),
            BodaccEventType::Fusion->value => (float) ($weights['fusion'] ?? 0),
            BodaccEventType::ChangementDirigeant->value => (float) ($weights['changement_dirigeant'] ?? 0),
            BodaccEventType::Creation->value => (float) ($weights['creation'] ?? 0),
        ];

        $total = 0.0;
        $detail = [];

        foreach ($in->bodaccEvents as $event) {
            $base = $typeWeight[$event->type->value] ?? 0.0;
            if ($base <= 0) {
                continue;
            }
            $ageMonths = $event->ageInMonths();
            // Décroissance exponentielle e^(-Δm/decay).
            $points = $base * exp(-$ageMonths / max($decay, 0.01));
            $total += $points;
            $detail[$event->type->value] = round(($detail[$event->type->value] ?? 0) + $points, 2);
        }

        $signaux = (int) round(min($cap, $total));

        return ['signaux' => $signaux, 'detail' => $detail];
    }

    private function confidence(ProspectInput $in): int
    {
        $weights = (array) config('prospects.scoring.confidence_weights', []);
        $score = 0;

        if ($in->codeNaf !== null) {
            $score += (int) ($weights['naf'] ?? 0);
        }
        if ($in->trancheEffectif !== null) {
            $score += (int) ($weights['effectif'] ?? 0);
        }
        if ($in->dateNaissanceDirigeant !== null) {
            $score += (int) ($weights['age_dirigeant'] ?? 0);
        }
        if ($in->bodaccConsulted) {
            $score += (int) ($weights['bodacc'] ?? 0);
        }
        if ($in->financesAvailable) {
            $score += (int) ($weights['finances'] ?? 0);
        }

        return min(100, $score);
    }

    /**
     * Trouve la 1re bande min..max contenant $value.
     *
     * @param  list<array{min: int, max: int, points: int}>  $bands
     */
    private function matchBand(array $bands, int $value): int
    {
        foreach ($bands as $band) {
            if ($value >= ($band['min'] ?? 0) && $value <= ($band['max'] ?? PHP_INT_MAX)) {
                return (int) ($band['points'] ?? 0);
            }
        }

        return 0;
    }
}
