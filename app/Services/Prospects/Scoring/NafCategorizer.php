<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring;

/**
 * Classifie un code NAF en idéal / intermédiaire / exclu selon la config.
 *
 * La logique : on cherche le préfixe le plus long qui matche. Un code exclu
 * (ex. `64.20`) court-circuite l'évaluation, même si un préfixe plus court
 * (ex. `64`) serait idéal.
 */
final class NafCategorizer
{
    public const IDEAL = 'ideal';
    public const INTERMEDIATE = 'intermediate';
    public const EXCLUDED = 'excluded';
    public const DEFAULT = 'default';

    public function __construct(
        /** @var list<string> */
        private readonly array $idealPrefixes,
        /** @var list<string> */
        private readonly array $intermediatePrefixes,
        /** @var list<string> */
        private readonly array $excludedPrefixes,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            idealPrefixes: (array) config('prospects.scoring.naf.ideal_prefixes', []),
            intermediatePrefixes: (array) config('prospects.scoring.naf.intermediate_prefixes', []),
            excludedPrefixes: (array) config('prospects.scoring.naf.excluded_prefixes', []),
        );
    }

    /**
     * @return self::IDEAL|self::INTERMEDIATE|self::EXCLUDED|self::DEFAULT
     */
    public function categorize(?string $codeNaf): string
    {
        if ($codeNaf === null || $codeNaf === '') {
            return self::DEFAULT;
        }

        $normalized = strtoupper(str_replace(' ', '', $codeNaf));

        // Priorité 1 : exclusion (les plus spécifiques d'abord).
        if ($this->matchesAny($normalized, $this->excludedPrefixes)) {
            return self::EXCLUDED;
        }
        if ($this->matchesAny($normalized, $this->idealPrefixes)) {
            return self::IDEAL;
        }
        if ($this->matchesAny($normalized, $this->intermediatePrefixes)) {
            return self::INTERMEDIATE;
        }

        return self::DEFAULT;
    }

    public function points(?string $codeNaf): int
    {
        return match ($this->categorize($codeNaf)) {
            self::IDEAL => (int) config('prospects.scoring.naf.points_ideal', 30),
            self::INTERMEDIATE => (int) config('prospects.scoring.naf.points_intermediate', 15),
            self::EXCLUDED => (int) config('prospects.scoring.naf.points_excluded', 0),
            default => (int) config('prospects.scoring.naf.points_default', 8),
        };
    }

    /**
     * Vrai si le NAF est dans la cible « industriel/négoce » utilisée par certains modificateurs.
     */
    public function isIndustrielOuNegoce(?string $codeNaf): bool
    {
        if ($codeNaf === null) {
            return false;
        }
        $n = strtoupper(str_replace(' ', '', $codeNaf));
        // Préfixes 10..33 (industrie) + 46 (négoce).
        if (preg_match('/^(1\d|2\d|3[0-3]|46)/', $n) === 1) {
            return true;
        }

        return false;
    }

    /**
     * Préfixe de secteur B2B (services aux entreprises / logistique / industrie).
     */
    public function isB2B(?string $codeNaf): bool
    {
        if ($codeNaf === null) {
            return false;
        }
        $n = strtoupper(str_replace(' ', '', $codeNaf));

        return preg_match('/^(1\d|2\d|3[0-3]|4[3569]|5[0-3]|6[23]|7[0-4]|8[02])/', $n) === 1;
    }

    /**
     * @param  list<string>  $prefixes
     */
    private function matchesAny(string $code, array $prefixes): bool
    {
        foreach ($prefixes as $p) {
            $normalized = strtoupper(str_replace(' ', '', $p));
            if ($normalized !== '' && str_starts_with($code, $normalized)) {
                return true;
            }
        }

        return false;
    }
}
