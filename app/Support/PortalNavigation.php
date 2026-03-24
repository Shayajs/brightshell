<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Liste des portails (labels, liens) et filtrage par rôles — partagé layout + page d’accueil portail.
 */
final class PortalNavigation
{
    /**
     * Tracés SVG (contenu interne stroke) par clé portail — switcher, cartes d’accueil, etc.
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'admin' => '<path d="M12 3l7 4v10l-7 4-7-4V7l7-4z" stroke-linejoin="round"/><path d="M12 12l7-4M12 12v10M12 12L5 8"/>',
            'collabs' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'users' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
            'courses' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
            'docs' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8M8 11h6"/>',
            'project' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>',
        ];
    }

    public static function iconSvg(string $key): string
    {
        return self::icons()[$key] ?? self::icons()['settings'];
    }

    /** Classes Tailwind : fond / anneau « logo » pour une clé portail. */
    public static function iconBadgeClasses(string $key): string
    {
        return match ($key) {
            'home' => 'bg-indigo-500/20 text-indigo-200 ring-indigo-400/35',
            'admin' => 'bg-violet-500/20 text-violet-200 ring-violet-400/35',
            'collabs' => 'bg-emerald-500/20 text-emerald-200 ring-emerald-400/35',
            'users' => 'bg-sky-500/20 text-sky-200 ring-sky-400/35',
            'courses' => 'bg-amber-500/20 text-amber-200 ring-amber-400/35',
            'settings' => 'bg-zinc-500/25 text-zinc-200 ring-zinc-400/30',
            'docs' => 'bg-blue-500/20 text-blue-200 ring-blue-400/35',
            'project' => 'bg-cyan-500/20 text-cyan-200 ring-cyan-400/35',
            default => 'bg-zinc-500/25 text-zinc-200 ring-zinc-400/30',
        };
    }

    /**
     * @return array<string, array{label: string, href: string, role: string|null}>
     */
    public static function allPortals(): array
    {
        $portals = [];

        $home = PortalUrls::homeUrl();
        if ($home !== '') {
            $portals['home'] = ['label' => 'Accueil', 'href' => $home, 'role' => null];
        }

        $portals['admin'] = ['label' => 'Administration', 'href' => PortalUrls::forRoleSlug('admin'), 'role' => 'admin'];
        $portals['collabs'] = ['label' => 'Collaborateurs', 'href' => PortalUrls::forRoleSlug('collaborator'), 'role' => 'collaborator'];
        $portals['users'] = ['label' => 'Espace client', 'href' => PortalUrls::forRoleSlug('client'), 'role' => 'client'];
        $portals['courses'] = ['label' => 'Cours', 'href' => PortalUrls::forRoleSlug('student'), 'role' => 'student'];
        $portals['docs'] = ['label' => 'Documentation', 'href' => PortalUrls::docsUrl(), 'role' => null];

        $projectUrl = PortalUrls::projectUrl();
        if ($projectUrl !== '') {
            $portals['project'] = ['label' => 'Projets', 'href' => $projectUrl, 'role' => 'project_portal'];
        }

        // Toujours en fin de liste dans le switcher.
        $portals['settings'] = ['label' => 'Réglages', 'href' => PortalUrls::settingsUrl(), 'role' => null];

        return $portals;
    }

    /**
     * Portails accessibles pour l’utilisateur connecté (pour le switcher et la page home).
     *
     * @return array<string, array{label: string, href: string, role: string|null}>
     */
    public static function accessiblePortals(?User $user): array
    {
        $all = self::allPortals();

        if ($user === null) {
            return [];
        }

        $isAdmin = $user->isAdmin() || $user->hasRole('admin');

        return Collection::make($all)
            ->filter(function (array $portal) use ($user, $isAdmin): bool {
                if ($portal['role'] === null) {
                    return true;
                }
                if ($portal['role'] === 'project_portal') {
                    if ($isAdmin) {
                        return true;
                    }

                    return Project::query()->accessibleByNonAdmin($user)->exists();
                }
                if ($isAdmin) {
                    return true;
                }

                return $user->hasRole($portal['role']);
            })
            ->all();
    }
}
