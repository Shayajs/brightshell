<?php

namespace App\Support;

use App\Models\SiteAppearance;

/**
 * Identité visuelle vitrine : chemins relatifs à public/ (admin > .env > défauts config).
 */
final class BrightshellBrand
{
    public static function faviconPath(): string
    {
        $row = SiteAppearance::query()->first();
        if ($row !== null && is_string($row->favicon_path) && $row->favicon_path !== '') {
            return $row->favicon_path;
        }

        return (string) config('brightshell.brand.favicon');
    }

    public static function siteLogoPath(): string
    {
        $row = SiteAppearance::query()->first();
        if ($row !== null && is_string($row->site_logo_path) && $row->site_logo_path !== '') {
            return $row->site_logo_path;
        }

        return (string) config('brightshell.brand.site_logo');
    }

    public static function faviconUrl(): string
    {
        return asset(self::faviconPath());
    }

    public static function faviconMimeType(): string
    {
        $path = self::faviconPath();
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            default => 'image/png',
        };
    }

    public static function siteLogoUrl(): string
    {
        return asset(self::siteLogoPath());
    }
}
