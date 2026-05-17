<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : ≥ N établissements actifs (gestion multi-sites, stock, planning).
 */
final class MultiEtablissementsDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'multi_etablissements';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $min = (int) $this->config('min_etablissements', 2);
        $max = (int) $this->config('max_etablissements', 8);

        return $in->nombreEtablissements >= $min && $in->nombreEtablissements <= $max;
    }

    protected function why(ProspectInput $in): string
    {
        return "Plusieurs établissements ({$in->nombreEtablissements}) — gestion multi-sites à outiller (stock, planning, reporting).";
    }

    protected function context(ProspectInput $in): array
    {
        return ['nombre_etablissements' => $in->nombreEtablissements];
    }
}
