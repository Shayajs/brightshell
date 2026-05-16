<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring\Dto;

/**
 * Adresse géocodée par l'API BAN (Base Adresse Nationale).
 */
final readonly class GeocodedAddress
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?string $codeInseeCommune,
        public ?string $ville,
        public ?string $codePostal,
        public float $score,
    ) {}

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'code_insee_commune' => $this->codeInseeCommune,
            'ville' => $this->ville,
            'code_postal' => $this->codePostal,
            'score' => $this->score,
        ];
    }
}
