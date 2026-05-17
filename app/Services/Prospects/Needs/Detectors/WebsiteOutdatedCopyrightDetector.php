<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : le site est vivant mais le copyright est vieux ≥ N ans (N configurable).
 *
 * Heuristique très utilisée par les commerciaux : un footer 2021 = site abandonné.
 */
final class WebsiteOutdatedCopyrightDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'website_outdated_copyright';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $snap = $in->websiteSnapshot;
        if ($snap === null || ! $snap->probed || $snap->alive !== true) {
            return null;
        }

        $age = $snap->ageYears();
        if ($age === null) {
            return null;
        }

        $min = (int) $this->config('min_age_years', 3);

        return $age >= $min;
    }

    protected function why(ProspectInput $in): string
    {
        $age = $in->websiteSnapshot?->ageYears();

        return $age !== null
            ? "Copyright du site daté de {$age} ans — site probablement à l'abandon."
            : 'Copyright du site obsolète — site probablement à l\'abandon.';
    }

    protected function context(ProspectInput $in): array
    {
        return [
            'copyright_year' => $in->websiteSnapshot?->copyrightYear,
            'age_years' => $in->websiteSnapshot?->ageYears(),
        ];
    }
}
