<?php

declare(strict_types=1);

namespace App\Actions\Prospects;

/**
 * Bilan d'un import (pour CLI et UI).
 */
final readonly class ImportResult
{
    public function __construct(
        public int $fetched,
        public int $kept,
        public int $excluded,
        /** @var array<string, int> */
        public array $byBand,
        /** @var array<int, int> */
        public array $byNiveau,
        /** @var array<string, int> */
        public array $byModifier,
        public ?string $csvPath,
        public int $durationMs,
        /** @var array<string, int> */
        public array $byNeed = [],
    ) {}

    public function toArray(): array
    {
        return [
            'fetched' => $this->fetched,
            'kept' => $this->kept,
            'excluded' => $this->excluded,
            'by_band' => $this->byBand,
            'by_niveau' => $this->byNiveau,
            'by_modifier' => $this->byModifier,
            'by_need' => $this->byNeed,
            'csv_path' => $this->csvPath,
            'duration_ms' => $this->durationMs,
        ];
    }
}
