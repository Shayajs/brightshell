<?php

declare(strict_types=1);

namespace App\Services\Prospects;

/**
 * Ordre des tranches effectif INSEE (Recherche Entreprises / Sirene).
 *
 * @see https://www.sirene.fr/sirene/publicat/vocabulary/tranche-effectif-salarie
 */
final class EffectifTranche
{
    /** @var list<string> */
    private const ORDER = [
        'NN', '00', '01', '02', '03', '11', '12', '21', '22', '31', '32', '41', '42', '51', '52', '53',
    ];

    public static function rank(?string $code): ?int
    {
        if ($code === null || $code === '') {
            return null;
        }

        $idx = array_search($code, self::ORDER, true);

        return $idx === false ? null : $idx;
    }

    /**
     * Vrai si la tranche est strictement supérieure à $maxCode (ex. max 12 = 20–49 sal. max).
     */
    public static function exceeds(?string $code, string $maxCode): bool
    {
        $rank = self::rank($code);
        $maxRank = self::rank($maxCode);

        if ($rank === null || $maxRank === null) {
            return false;
        }

        return $rank > $maxRank;
    }

    /**
     * Vrai si la tranche est comprise entre $minCode et $maxCode (inclus).
     */
    public static function between(?string $code, string $minCode, string $maxCode): bool
    {
        $rank = self::rank($code);
        $minRank = self::rank($minCode);
        $maxRank = self::rank($maxCode);

        if ($rank === null || $minRank === null || $maxRank === null) {
            return false;
        }

        return $rank >= $minRank && $rank <= $maxRank;
    }
}
