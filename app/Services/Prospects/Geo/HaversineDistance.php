<?php

declare(strict_types=1);

namespace App\Services\Prospects\Geo;

/**
 * Distance Haversine (à vol d'oiseau) entre deux points GPS, en kilomètres.
 *
 * Rayon Terre : 6371 km. Précision suffisante pour le ciblage commercial régional.
 */
final class HaversineDistance
{
    public const EARTH_RADIUS_KM = 6371.0;

    public static function kilometers(float $lat1, float $long1, float $lat2, float $long2): float
    {
        $latRad1 = deg2rad($lat1);
        $latRad2 = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLong = deg2rad($long2 - $long1);

        $a = sin($deltaLat / 2) ** 2
            + cos($latRad1) * cos($latRad2) * sin($deltaLong / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }
}
