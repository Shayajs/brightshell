<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;
use App\Services\Prospects\Scoring\NafCategorizer;

/**
 * Besoin : effectif ≥ X (config) en secteur B2B (services aux entreprises / industrie / négoce).
 *
 * Profil typique pour vendre un ERP, un CRM, un outil RH ou de gestion projet.
 */
final class EffectifEleveB2BDetector extends AbstractConfigurableDetector
{
    public function __construct(
        private readonly NafCategorizer $naf,
    ) {}

    protected function key(): string
    {
        return 'effectif_eleve_b2b';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $minCode = (string) $this->config('min_effectif_code', '12');
        if ($in->trancheEffectif === null) {
            return null;
        }
        // Comparaison alphabétique sur le code INSEE (00 < 01 < 02 < 03 < 11 < 12 < 21 < 22 …).
        if (strcmp($in->trancheEffectif, $minCode) < 0) {
            return false;
        }

        return $this->naf->isB2B($in->codeNaf);
    }

    protected function why(ProspectInput $in): string
    {
        return 'Effectif élevé en secteur B2B — terrain idéal pour ERP / CRM / outils internes.';
    }

    protected function context(ProspectInput $in): array
    {
        return ['effectif_code' => $in->trancheEffectif];
    }
}
