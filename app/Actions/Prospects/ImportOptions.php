<?php

declare(strict_types=1);

namespace App\Actions\Prospects;

/**
 * Options d'import (CLI ou Livewire).
 */
final readonly class ImportOptions
{
    public function __construct(
        public ?string $codePostal = null,
        public ?string $departement = null,
        public ?string $codeNaf = null,
        public int $maxPages = 10,
        public int $perPage = 25,
        public bool $withCsv = true,
        public bool $withBodacc = true,
        public bool $withGeocoding = true,
        public bool $withInpi = false,
        public bool $withWebsiteProbe = true,
        public string $minBand = 'watch',
        public ?string $importId = null,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toApiFilters(): array
    {
        $filters = [
            'code_postal' => $this->codePostal,
            'departement' => $this->departement,
            'activite_principale' => $this->codeNaf,
        ];

        $tranches = config('prospects.import.tranche_effectif_salarie');
        if (is_string($tranches) && $tranches !== '') {
            $filters['tranche_effectif_salarie'] = $tranches;
        }

        return array_filter($filters, static fn ($v) => $v !== null && $v !== '');
    }

    public function zoneLabel(): string
    {
        return $this->codePostal
            ?? ($this->departement !== null ? "dep{$this->departement}" : 'all');
    }
}
