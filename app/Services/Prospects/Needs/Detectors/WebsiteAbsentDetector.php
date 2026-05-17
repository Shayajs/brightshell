<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : aucun site web déclaré.
 *
 * Bonus le plus fort de la catégorie web — c'est le besoin le plus évident à vendre.
 */
final class WebsiteAbsentDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'website_absent';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        return empty($in->siteInternet);
    }

    protected function why(ProspectInput $in): string
    {
        return 'Aucun site web renseigné dans les données publiques — opportunité refonte ou lancement.';
    }
}
