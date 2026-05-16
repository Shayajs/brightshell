<?php

declare(strict_types=1);

namespace App\Services\Prospects;

/**
 * Résout l'URL d'un logo via Clearbit (sans API key pour usage léger).
 *
 * Stratégie :
 * 1. Si on a un `site_internet` → on en extrait le domaine.
 * 2. Sinon, si l'`email_contact` n'est pas générique → on prend son domaine.
 * 3. Sinon → null (la vue fallback sur un monogramme).
 *
 * @see https://clearbit.com/logo
 */
final class ClearbitLogoResolver
{
    /**
     * Déduit le domaine web exploitable pour Clearbit.
     */
    public function resolveDomain(?string $siteInternet, ?string $emailContact): ?string
    {
        $fromSite = $this->extractDomainFromUrl($siteInternet);
        if ($fromSite !== null) {
            return $fromSite;
        }

        $fromEmail = $this->extractDomainFromEmail($emailContact);
        if ($fromEmail !== null && ! $this->isGenericEmailDomain($fromEmail)) {
            return $fromEmail;
        }

        return null;
    }

    public function logoUrl(?string $domain, int $size = 80): ?string
    {
        if ($domain === null || $domain === '') {
            return null;
        }

        return "https://logo.clearbit.com/{$domain}?size={$size}";
    }

    private function extractDomainFromUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            $host = preg_replace('#^https?://#i', '', $url) ?? '';
            $host = strtok($host, '/') ?: $host;
        }

        $host = strtolower(trim($host));
        $host = preg_replace('/^www\./', '', $host) ?? '';

        return $host !== '' && str_contains($host, '.') ? $host : null;
    }

    private function extractDomainFromEmail(?string $email): ?string
    {
        if ($email === null || ! str_contains($email, '@')) {
            return null;
        }
        $domain = strtolower(trim(substr(strrchr($email, '@') ?: '', 1)));

        return $domain !== '' && str_contains($domain, '.') ? $domain : null;
    }

    private function isGenericEmailDomain(string $domain): bool
    {
        $generic = (array) config('prospects.modifiers.digital_gap.emails_generiques', []);

        return in_array($domain, $generic, true);
    }
}
