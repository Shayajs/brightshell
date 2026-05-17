<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : le site n'a pas de `<meta name="viewport"...width=device-width>`.
 *
 * Indicateur fiable d'un site pré-2014 (avant l'adoption massive du responsive).
 */
final class WebsiteNonResponsiveDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'website_non_responsive';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $snap = $in->websiteSnapshot;
        if ($snap === null || ! $snap->probed || $snap->alive !== true) {
            return null;
        }

        return $snap->responsive === false;
    }

    protected function why(ProspectInput $in): string
    {
        return 'Site non responsive (meta viewport absente) — illisible sur mobile, ~60 % du trafic perdu.';
    }
}
