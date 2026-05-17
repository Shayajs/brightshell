<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : le site est déclaré mais injoignable (HTTP 4xx/5xx).
 *
 * Comportement complémentaire à WebsiteAbsentDetector : si le site est listé
 * mais qu'on ne peut pas l'atteindre, le besoin "refonte" est évident.
 */
final class WebsiteDeadDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'website_dead';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        if (empty($in->siteInternet) || $in->websiteSnapshot === null || ! $in->websiteSnapshot->probed) {
            return null;
        }

        return $in->websiteSnapshot->alive === false;
    }

    protected function why(ProspectInput $in): string
    {
        $code = $in->websiteSnapshot?->statusCode;

        return $code !== null
            ? "Site déclaré mais injoignable (HTTP {$code}) — perte de visibilité directe."
            : 'Site déclaré mais inatteignable depuis nos sondes — perte de visibilité directe.';
    }

    protected function context(ProspectInput $in): array
    {
        return ['status_code' => $in->websiteSnapshot?->statusCode];
    }
}
