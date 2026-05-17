<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Besoin : l'email de contact public est sur un domaine générique (gmail, free, orange…).
 *
 * Signal fort pour vendre une identité de marque (email pro `prenom@entreprise.fr`).
 * Réutilise la liste `prospects.modifiers.digital_gap.emails_generiques` pour cohérence.
 */
final class GenericEmailDetector extends AbstractConfigurableDetector
{
    protected function key(): string
    {
        return 'email_generique';
    }

    protected function passes(ProspectInput $in): ?bool
    {
        $domain = $in->emailDomain();
        if ($domain === null) {
            return null;
        }
        $generic = (array) config('prospects.modifiers.digital_gap.emails_generiques', []);

        return in_array($domain, $generic, true);
    }

    protected function why(ProspectInput $in): string
    {
        return "Email de contact sur domaine grand public ({$in->emailDomain()}) — branding amateur.";
    }

    protected function context(ProspectInput $in): array
    {
        return ['email_domain' => $in->emailDomain()];
    }
}
