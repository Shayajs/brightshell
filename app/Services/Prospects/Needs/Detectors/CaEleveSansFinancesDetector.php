<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : effectif moyen (6-19 salariés) avec besoin probable d'automatisation
 * comptable / RH / paie / suivi temps.
 *
 * Calibre conservateur : on cible la transition "j'ai trop d'Excel pour le faire à la main"
 * sans déclencher sur les TPE solo (effectif 00-02).
 */
final class CaEleveSansFinancesDetector extends AbstractConfigurableDetector
{
    private const EFFECTIFS_CIBLE = ['03', '11']; // 6-9 et 10-19 salariés

    protected function key(): string
    {
        return 'ca_eleve_sans_finances';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        if ($in->trancheEffectif === null) {
            return null;
        }

        return in_array($in->trancheEffectif, self::EFFECTIFS_CIBLE, true);
    }

    protected function why(ProspectInput $in): string
    {
        return 'Effectif moyen (6-19 salariés) — bascule typique vers compta / paie / planning outillés.';
    }
}
