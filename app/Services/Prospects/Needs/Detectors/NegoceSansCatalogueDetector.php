<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : commerce de gros / négoce (NAF 46.*) sans site marchand / catalogue.
 *
 * Hypothèse commerciale : un grossiste sans catalogue en ligne perd des ventes B2B.
 * On considère qu'il n'a pas de catalogue si le site est absent OU mort.
 */
final class NegoceSansCatalogueDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'negoce_sans_catalogue';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $naf = strtoupper(str_replace(' ', '', (string) $in->codeNaf));
        if (! str_starts_with($naf, '46')) {
            return false;
        }

        // Pas de site OU site mort = pas de catalogue.
        if (empty($in->siteInternet)) {
            return true;
        }
        if ($in->websiteSnapshot !== null && $in->websiteSnapshot->probed && $in->websiteSnapshot->alive === false) {
            return true;
        }

        return false;
    }

    protected function why(ProspectInput $in): string
    {
        return 'Négoce / commerce de gros sans catalogue en ligne — opportunité de catalogue B2B ou de boutique de réassort.';
    }
}
