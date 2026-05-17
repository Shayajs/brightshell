<?php

declare(strict_types=1);

namespace App\Services\Prospects\Web;

/**
 * Snapshot léger du site web d'un prospect issu de {@see WebsiteProbe}.
 *
 * Toutes les détections sont best-effort (regex sur le HTML) — la `confidence`
 * informe les détecteurs sur la fiabilité du résultat.
 */
final readonly class WebsiteSnapshot
{
    public function __construct(
        public bool $probed,
        public ?bool $alive = null,
        public ?int $statusCode = null,
        public ?bool $https = null,
        public ?bool $responsive = null,
        public ?string $platform = null,
        public ?string $platformVersion = null,
        public ?int $copyrightYear = null,
        public ?string $finalUrl = null,
        public int $confidence = 0,
    ) {}

    public static function notProbed(): self
    {
        return new self(probed: false);
    }

    public static function dead(int $statusCode): self
    {
        return new self(
            probed: true,
            alive: false,
            statusCode: $statusCode,
            confidence: 80,
        );
    }

    public function ageYears(?int $reference = null): ?int
    {
        if ($this->copyrightYear === null) {
            return null;
        }
        $ref = $reference ?? (int) date('Y');

        return max(0, $ref - $this->copyrightYear);
    }

    /**
     * Heuristique "site vieillissant" : copyright ≥ 3 ans OU pas responsive
     * OU plateforme historiquement obsolète.
     */
    public function isOutdated(): bool
    {
        if ($this->alive === false) {
            return false; // mort, pas "vieux"
        }
        $age = $this->ageYears();
        if ($age !== null && $age >= 3) {
            return true;
        }
        if ($this->responsive === false) {
            return true;
        }
        if ($this->platform !== null && in_array($this->platform, ['Wix-classic', 'Jimdo-classic', 'Site123', 'Sitew', 'e-monsite'], true)) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'probed' => $this->probed,
            'alive' => $this->alive,
            'status_code' => $this->statusCode,
            'https' => $this->https,
            'responsive' => $this->responsive,
            'platform' => $this->platform,
            'platform_version' => $this->platformVersion,
            'copyright_year' => $this->copyrightYear,
            'final_url' => $this->finalUrl,
            'confidence' => $this->confidence,
        ];
    }
}
