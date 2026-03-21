<?php

namespace App\Support\PublicApi;

use App\Support\BrightshellDomain;
use Illuminate\Support\Facades\Route;

/**
 * Hôte et URLs de l’API publique (sous-domaine api.*), aligné sur bootstrap/app.php.
 */
final class PublicApiSupport
{
    public static function resolvedHost(): ?string
    {
        $host = trim((string) config('brightshell.domains.api_host', ''));
        $root = BrightshellDomain::effectiveRoot();
        if ($host === '' && $root !== '') {
            $host = 'api.'.$root;
        }

        return $host !== '' ? $host : null;
    }

    public static function isEnabled(): bool
    {
        return self::resolvedHost() !== null;
    }

    /**
     * Origine de l’API sans slash final (ex. https://api.example.com).
     */
    public static function rootUrl(): ?string
    {
        $host = self::resolvedHost();
        if ($host === null) {
            return null;
        }

        return BrightshellDomain::urlScheme().'://'.$host;
    }

    public static function absoluteUrl(string $path): ?string
    {
        $root = self::rootUrl();
        if ($root === null) {
            return null;
        }

        return $root.'/'.ltrim($path, '/');
    }

    public static function namedRouteUrl(string $name): ?string
    {
        if (! Route::has($name)) {
            return null;
        }

        try {
            return route($name, [], true);
        } catch (\Throwable) {
            return null;
        }
    }
}
