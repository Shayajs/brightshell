<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring;

use App\Services\Prospects\Scoring\Dto\ProspectInput;
use App\Services\Prospects\Scoring\Dto\ScoreResult;

/**
 * Orchestrateur du moteur de scoring.
 *
 * Pipeline :
 *   1. {@see BasePointsCalculator} → points bruts /100
 *   2. {@see NonLinearModifiers}   → multiplicateurs croisés + bonus secs
 *   3. Application différenciée par cible (global / website / software)
 *   4. {@see ScoreBandsClassifier} → bande Hot/Priority/…
 *
 * Le résultat est sérialisable (cf. `breakdown`) pour l'explicabilité dans l'UI.
 */
final class ScoreEngine
{
    public function __construct(
        private readonly BasePointsCalculator $base,
        private readonly NonLinearModifiers $modifiers,
        private readonly ScoreBandsClassifier $bands,
    ) {}

    public function compute(ProspectInput $in): ScoreResult
    {
        $base = $this->base->compute($in);
        $brut = $base['naf'] + $base['structure'] + $base['gouvernance'] + $base['signaux'];
        $mods = $this->modifiers->detect($in);

        // Véto absolu → ScoreResult::excluded(...)
        if (isset($mods['veto.procedure_collective'])) {
            return ScoreResult::excluded($mods, $base['confidence'], $base);
        }

        $website = $this->apply($brut, $mods, target: 'website');
        $software = $this->apply($brut, $mods, target: 'software');
        $global = max($this->apply($brut, $mods, target: 'global'), $website, $software);

        $band = $this->bands->classify($global);

        return new ScoreResult(
            scoreGlobal: $global,
            scoreWebsite: $website,
            scoreSoftware: $software,
            band: $band,
            niveauLegacy: $band->legacyNiveau(),
            confidence: $base['confidence'],
            breakdown: [
                'base' => $base,
                'modifiers' => $mods,
                'brut' => $brut,
                'targets' => [
                    'global' => $global,
                    'website' => $website,
                    'software' => $software,
                ],
            ],
        );
    }

    /**
     * Applique multiplicateurs et bonus secs pour une cible donnée.
     *
     * @param  array<string, array{multiplier: float, flat_bonus: int, targets: list<string>, why: string}>  $mods
     */
    private function apply(int $brut, array $mods, string $target): int
    {
        $score = (float) $brut;
        $bonusFlat = 0;

        // 1) Multiplicateurs (l'ordre n'a pas d'importance vu qu'on multiplie tout).
        foreach ($mods as $mod) {
            if (! $this->matchesTarget($mod['targets'] ?? [], $target)) {
                continue;
            }
            $multiplier = (float) ($mod['multiplier'] ?? 1.0);
            if ($multiplier !== 1.0) {
                $score *= $multiplier;
            }
            $bonusFlat += (int) ($mod['flat_bonus'] ?? 0);
        }

        $final = (int) round($score + $bonusFlat);

        // Plafond raisonnable : 200 (utile pour ne pas exploser le badge UI).
        return max(0, min(200, $final));
    }

    /**
     * @param  list<string>  $modTargets
     */
    private function matchesTarget(array $modTargets, string $current): bool
    {
        return in_array('*', $modTargets, true) || in_array($current, $modTargets, true);
    }
}
