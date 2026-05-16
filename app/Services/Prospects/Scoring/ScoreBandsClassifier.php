<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring;

/**
 * Classifie un score quantitatif en bande {@see ScoreBand}.
 *
 * Seuils pilotés par `config/prospects.php` → `scoring.bands`.
 */
final class ScoreBandsClassifier
{
    public function classify(int $score): ScoreBand
    {
        $thresholds = (array) config('prospects.scoring.bands', []);

        return match (true) {
            $score >= (int) ($thresholds['hot'] ?? 120) => ScoreBand::Hot,
            $score >= (int) ($thresholds['priority'] ?? 80) => ScoreBand::Priority,
            $score >= (int) ($thresholds['standard'] ?? 50) => ScoreBand::Standard,
            $score >= (int) ($thresholds['watch'] ?? 25) => ScoreBand::Watch,
            default => ScoreBand::Excluded,
        };
    }
}
