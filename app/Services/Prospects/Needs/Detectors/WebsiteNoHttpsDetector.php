<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : le site est servi en HTTP (pas de HTTPS).
 *
 * SEO pénalisé + cadenas barré dans les navigateurs = sujet de refonte simple à vendre.
 */
final class WebsiteNoHttpsDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'website_no_https';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $snap = $in->websiteSnapshot;
        if ($snap === null || ! $snap->probed || $snap->alive !== true) {
            return null;
        }

        return $snap->https === false;
    }

    protected function why(ProspectInput $in): string
    {
        return 'Site servi en HTTP — alerte de sécurité navigateur et pénalité SEO.';
    }
}
