<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring\Dto;

use App\Services\Prospects\Scoring\ScoreBand;

/**
 * Résultat complet du moteur de scoring pour un prospect.
 *
 * `breakdown` est le JSON persisté en BDD pour l'explicabilité (UI radar + chips).
 */
final readonly class ScoreResult
{
    public function __construct(
        public int $scoreGlobal,
        public int $scoreWebsite,
        public int $scoreSoftware,
        public ScoreBand $band,
        public int $niveauLegacy,
        public int $confidence,
        /** @var array<string, mixed> */
        public array $breakdown,
    ) {}

    /**
     * Cas véto : score à 0 et bande Excluded.
     *
     * @param array<string, mixed> $modifiers
     */
    public static function excluded(array $modifiers, int $confidence, array $base = []): self
    {
        return new self(
            scoreGlobal: 0,
            scoreWebsite: 0,
            scoreSoftware: 0,
            band: ScoreBand::Excluded,
            niveauLegacy: 0,
            confidence: $confidence,
            breakdown: [
                'base' => $base,
                'modifiers' => $modifiers,
                'brut' => 0,
                'reason' => 'Véto : procédure collective active.',
            ],
        );
    }
}
