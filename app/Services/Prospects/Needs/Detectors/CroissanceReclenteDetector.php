<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\BodaccEventType;
use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin transverse : croissance récente (augmentation de capital + déménagement
 * dans les 12 derniers mois).
 *
 * Signal fort de phase d'expansion → budget marketing + outils internes en hausse.
 */
final class CroissanceReclenteDetector extends AbstractConfigurableDetector
{
    private const FENETRE_MOIS = 12;

    protected function key(): string
    {
        return 'croissance_recente';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        if (! $in->bodaccConsulted) {
            return null;
        }

        $augmentation = false;
        $demenagement = false;
        foreach ($in->bodaccEvents as $event) {
            if ($event->ageInMonths() > self::FENETRE_MOIS) {
                continue;
            }
            if ($event->type === BodaccEventType::AugmentationCapital) {
                $augmentation = true;
            }
            if ($event->type === BodaccEventType::Demenagement) {
                $demenagement = true;
            }
        }

        return $augmentation && $demenagement;
    }

    protected function why(ProspectInput $in): string
    {
        return 'Augmentation de capital + déménagement récents : phase d\'expansion active.';
    }
}
