<?php

namespace App\Support;

/**
 * Domaine racine effectif pour les sous-domaines (admin.*, courses.*, …).
 *
 * Ordre : BRIGHTSHELL_ROOT_DOMAIN si défini, sinon hôte de APP_URL (sans www.).
 */
final class BrightshellDomain
{
    public static function effectiveRoot(): string
    {
        $configured = trim((string) config('brightshell.domains.root', ''));
        if ($configured !== '') {
            return $configured;
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return '';
        }

        $host = (string) preg_replace('/^www\./i', '', $host);

        return $host;
    }

    /**
     * Schéma (http/https) cohérent avec APP_URL.
     */
    public static function urlScheme(): string
    {
        return parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';
    }
}
