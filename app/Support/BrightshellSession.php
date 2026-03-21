<?php

namespace App\Support;

/**
 * Session unique sur tout le domaine (évite les doublons brightshell-session sur admin.* vs .domain).
 */
final class BrightshellSession
{
    /**
     * À appeler tôt au boot (avant StartSession) : fige session.domain sur ".{root}".
     */
    public static function applySharedCookieDomain(): void
    {
        $root = BrightshellDomain::effectiveRoot();

        if ($root === '' || $root === 'localhost' || str_starts_with($root, '127.')) {
            return;
        }

        $domain = '.'.ltrim($root, '.');

        config([
            'session.domain' => $domain,
        ]);
    }
}
