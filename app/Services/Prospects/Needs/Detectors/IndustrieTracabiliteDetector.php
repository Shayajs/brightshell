<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : industrie manufacturière (NAF 10..33).
 *
 * Profil typique pour vendre du suivi de production, de la traçabilité (MES),
 * de la maintenance préventive, de la GMAO ou des dashboards opérationnels.
 *
 * On exige un effectif ≥ 6 salariés pour filtrer les artisans solo.
 */
final class IndustrieTracabiliteDetector extends AbstractConfigurableDetector
{
    private const MIN_EFFECTIF = '03'; // 6-9 salariés

    protected function key(): string
    {
        return 'industrie_traceabilite';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $naf = strtoupper(str_replace(' ', '', (string) $in->codeNaf));
        if (preg_match('/^(1\d|2\d|3[0-3])/', $naf) !== 1) {
            return false;
        }
        if ($in->trancheEffectif === null) {
            return null;
        }

        return strcmp($in->trancheEffectif, self::MIN_EFFECTIF) >= 0;
    }

    protected function why(ProspectInput $in): string
    {
        return 'Industrie manufacturière avec atelier — terrain pour MES, suivi production, traçabilité.';
    }
}
