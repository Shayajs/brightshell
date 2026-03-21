<?php

namespace App\Support;

final class BrightshellAccount
{
    public static function loginUrl(): string
    {
        $base = (string) config('brightshell.account.base_url', '');
        $path = (string) config('brightshell.account.login_path', '/login');

        if ($base !== '') {
            return self::joinBaseAndPath($base, $path);
        }

        $accountHost = (string) config('brightshell.domains.account_host', '');
        if ($accountHost === '') {
            $root = BrightshellDomain::effectiveRoot();
            if ($root !== '') {
                $accountHost = 'account.'.$root;
            }
        }

        if ($accountHost !== '') {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

            return $scheme.'://'.$accountHost.self::normalizePath($path);
        }

        return url(self::normalizePath($path));
    }

    /**
     * Landing après connexion (placeholder jusqu’au RoleResolver).
     */
    public static function defaultSpaceUrl(): string
    {
        $explicit = config('brightshell.account.post_login_url');
        if (is_string($explicit) && $explicit !== '') {
            return $explicit;
        }

        $base = (string) config('brightshell.account.base_url', '');
        $path = (string) config('brightshell.account.post_login_path', '/');

        if ($base !== '') {
            return self::joinBaseAndPath($base, $path);
        }

        if (filled(config('brightshell.domains.account_host'))) {
            return rtrim((string) config('app.url'), '/').'/';
        }

        return url(self::normalizePath($path));
    }

    private static function normalizePath(string $path): string
    {
        return '/'.ltrim($path, '/');
    }

    private static function joinBaseAndPath(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/').self::normalizePath($path);
    }
}
