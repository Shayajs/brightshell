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

    /**
     * Racine du site public (vitrine), toujours {@see config('app.url')} — pas l’hôte du portail courant.
     */
    public static function publicSiteUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /**
     * URL du webmail (sous-domaine mail), schéma aligné sur APP_URL.
     * Surcharge : BRIGHTSHELL_MAIL_WEB_HOST (hôte seul ou URL complète).
     */
    public static function mailWebUrl(): string
    {
        $configured = trim((string) config('brightshell.domains.mail_web_host', ''));
        if ($configured !== '') {
            if (str_starts_with($configured, 'http://') || str_starts_with($configured, 'https://')) {
                return rtrim($configured, '/');
            }

            return self::urlScheme().'://'.$configured;
        }

        $root = self::effectiveRoot();
        if ($root === '') {
            return '';
        }

        return self::urlScheme().'://mail.'.$root;
    }

    /**
     * Hôte effectif de l’API (même logique que le chargement des routes dans bootstrap/app.php).
     */
    public static function effectiveApiHost(): string
    {
        $apiHost = trim((string) config('brightshell.domains.api_host', ''));
        $root = self::effectiveRoot();
        if ($apiHost === '' && $root !== '') {
            $apiHost = 'api.'.$root;
        }

        return $apiHost;
    }
}
