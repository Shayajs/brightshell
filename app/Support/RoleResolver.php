<?php

namespace App\Support;

use App\Models\User;

/**
 * Redirection après connexion : plateforme du rôle à la priorité la plus haute.
 */
final class RoleResolver
{
    /**
     * URL du portail par défaut pour l’utilisateur (rôle max), ou vitrine si aucun rôle.
     */
    public static function defaultPortalUrl(User $user): string
    {
        $forced = config('brightshell.account.post_login_url');
        if (is_string($forced) && $forced !== '') {
            return $forced;
        }

        $home = PortalUrls::homeUrl();
        if ($home !== '') {
            return $home;
        }

        $role = $user->highestRole();

        if ($role === null) {
            return rtrim((string) config('app.url'), '/').'/';
        }

        return PortalUrls::forRoleSlug($role->slug);
    }
}
