<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Contrat d'un détecteur de besoin.
 *
 * Chaque détecteur est indépendant et fait UNE seule chose. Le `NeedsDetector`
 * les exécute dans l'ordre déclaré ; rien ne s'arrête si l'un retourne null.
 *
 * Le détecteur doit être pur (pas d'I/O) — les appels HTTP sont faits AVANT
 * par {@see \App\Services\Prospects\Web\WebsiteProbe}.
 */
interface Detector
{
    /**
     * @return NeedSignal|null  Null si le besoin ne s'applique pas à ce prospect.
     */
    public function detect(ProspectInput $in): ?NeedSignal;
}
