<?php

namespace App\Support;

/**
 * URLs des portails par rôle (sous-domaines ou URLs absolues .env).
 */
final class PortalUrls
{
    /**
     * URL de base du portail pour un slug de rôle (sans path métier).
     */
    public static function forRoleSlug(string $slug): string
    {
        $root = BrightshellDomain::effectiveRoot();
        $scheme = BrightshellDomain::urlScheme();

        return match ($slug) {
            'admin' => self::resolve(
                (string) config('brightshell.portals.admin_url', ''),
                'admin',
                $root,
                $scheme
            ),
            'collaborator' => self::resolve(
                (string) config('brightshell.portals.collabs_url', ''),
                'collabs',
                $root,
                $scheme
            ),
            'client' => self::resolve(
                (string) config('brightshell.portals.users_url', ''),
                'users',
                $root,
                $scheme
            ),
            'student' => self::resolve(
                (string) config('brightshell.portals.courses_url', ''),
                'courses',
                $root,
                $scheme
            ),
            default => rtrim((string) config('app.url'), '/').'/',
        };
    }

    public static function settingsUrl(): string
    {
        $root = BrightshellDomain::effectiveRoot();
        $scheme = BrightshellDomain::urlScheme();

        return self::resolve(
            (string) config('brightshell.portals.settings_url', ''),
            'settings',
            $root,
            $scheme
        );
    }

    public static function docsUrl(): string
    {
        $root = BrightshellDomain::effectiveRoot();
        $scheme = BrightshellDomain::urlScheme();

        return self::resolve(
            (string) config('brightshell.portals.docs_url', ''),
            'docs',
            $root,
            $scheme
        );
    }

    /**
     * Hub portail (home.{root}). Chaîne vide si aucun domaine racine multi-sous-domaines (ex. localhost simple).
     */
    public static function homeUrl(): string
    {
        $root = BrightshellDomain::effectiveRoot();
        if ($root === '') {
            return '';
        }

        $scheme = BrightshellDomain::urlScheme();

        return self::resolve(
            (string) config('brightshell.portals.home_url', ''),
            'home',
            $root,
            $scheme
        );
    }

    private static function resolve(string $explicitUrl, string $subdomain, string $rootDomain, string $scheme): string
    {
        if ($explicitUrl !== '') {
            return rtrim($explicitUrl, '/').'/';
        }

        if ($rootDomain !== '') {
            return $scheme.'://'.$subdomain.'.'.ltrim($rootDomain, '/').'/';
        }

        return rtrim((string) config('app.url'), '/').'/';
    }
}
