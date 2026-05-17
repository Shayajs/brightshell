<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : site bâti sur un constructeur historique (Wix Classic, Jimdo, Site123, e-monsite, Sitew).
 *
 * Ces plateformes ont des limites SEO, des designs datés et un ROI faible
 * en dehors d'une vitrine de base. Migration vers un vrai CMS = pitch facile.
 */
final class WebsiteLegacyBuilderDetector extends AbstractConfigurableDetector
{
    private const LEGACY = ['Wix-classic', 'Jimdo-classic', 'Site123', 'Sitew', 'e-monsite'];

    protected function key(): string
    {
        return 'website_legacy_builder';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $snap = $in->websiteSnapshot;
        if ($snap === null || ! $snap->probed || $snap->alive !== true) {
            return null;
        }

        return $snap->platform !== null && in_array($snap->platform, self::LEGACY, true);
    }

    protected function why(ProspectInput $in): string
    {
        $platform = $in->websiteSnapshot?->platform;

        return "Site bâti sur un constructeur historique ({$platform}) — limites SEO et design daté.";
    }

    protected function context(ProspectInput $in): array
    {
        return ['platform' => $in->websiteSnapshot?->platform];
    }
}
