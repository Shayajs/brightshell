@php
    use App\Models\Project;
    use App\Support\BrightshellBrand;
    use App\Support\BrightshellDomain;
    use App\Support\PortalNavigation;

    $host = strtolower((string) request()->getHost());
    $labels = explode('.', $host);
    $portalKey = 'admin';
    if (count($labels) >= 3) {
        $portalKey = match ($labels[0]) {
            'admin', 'collabs', 'users', 'courses', 'settings', 'docs', 'home', 'project' => $labels[0],
            default => 'admin',
        };
    } elseif (count($labels) === 2 && str_ends_with($host, '.localhost')) {
        $portalKey = match ($labels[0]) {
            'admin', 'collabs', 'users', 'courses', 'settings', 'docs', 'home', 'project' => $labels[0],
            default => 'admin',
        };
    }

    $u = auth()->user();

    $allPortals = PortalNavigation::allPortals();
    $accessiblePortals = PortalNavigation::accessiblePortals($u);

    $sidebarProjects = collect();
    if ($portalKey === 'project' && $u !== null) {
        $sidebarProjects = Project::query()
            ->forUser($u)
            ->orderedForDisplay()
            ->limit(25)
            ->get();
    }

    $currentPortalLabel = $allPortals[$portalKey]['label'] ?? 'Portail';
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portail') — BrightShell</title>
    <link rel="icon" href="{{ BrightshellBrand::faviconUrl() }}" type="{{ BrightshellBrand::faviconMimeType() }}">
    <link rel="preload" href="{{ asset('fonts/Gilroy-ExtraBold.otf') }}" as="font" type="font/otf" crossorigin>
    <style>
        @font-face {
            font-family: 'Gilroy ExtraBold';
            font-style: normal;
            font-weight: 800;
            font-display: swap;
            src: url('{{ asset('fonts/Gilroy-ExtraBold.otf') }}') format('opentype');
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/portal-shell.js'])
    @stack('vite')
    @stack('styles')
</head>
<body class="auth-body min-h-full bg-zinc-950 text-zinc-100 antialiased">
    <div class="pointer-events-none fixed inset-0 z-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(99,102,241,0.12),transparent)]" aria-hidden="true"></div>

    <div
        class="portal-shell group relative z-10 flex min-h-screen w-full items-start"
        data-portal="{{ $portalKey }}"
    >
        @unless ($portalKey === 'home')
        {{-- Backdrop mobile --}}
        <div
            class="portal-sidebar-backdrop fixed inset-0 z-[35] hidden bg-black/60 backdrop-blur-sm group-[.portal-sidebar-open]:block lg:!hidden"
            data-portal-sidebar-backdrop
            aria-hidden="true"
        ></div>

        {{-- ===================== SIDEBAR ===================== --}}
        <aside
            @class([
                'portal-sidebar fixed inset-y-0 left-0 z-40 grid h-dvh min-h-0 w-[min(18.5rem,88vw)] max-w-full shrink-0 -translate-x-full grid-rows-[auto_minmax(0,1fr)_auto] overflow-x-hidden overflow-y-hidden border-r bg-zinc-900/95 backdrop-blur-xl transition-transform duration-300 group-[.portal-sidebar-open]:translate-x-0 lg:sticky lg:top-0 lg:z-auto lg:h-dvh lg:max-w-none lg:translate-x-0',
                'border-zinc-800' => $portalKey !== 'docs',
                'border-indigo-500/15 bg-gradient-to-b from-zinc-900/98 to-zinc-950/98 ring-1 ring-inset ring-indigo-500/10' => $portalKey === 'docs',
                'lg:w-64' => $portalKey !== 'docs',
                'lg:w-[min(22rem,calc(100vw-2rem))] xl:w-[23rem] 2xl:w-[min(28rem,calc(100vw-3rem))]' => $portalKey === 'docs',
            ])
            aria-label="Navigation {{ $currentPortalLabel }}"
        >
            {{-- ┌───────────────────────────────────────────────────────── --}}
            {{-- │ Boîte 1 — Marque (hauteur contenu, jamais écrasée)          --}}
            {{-- └───────────────────────────────────────────────────────── --}}
            <div class="portal-sidebar-box-brand relative z-10 shrink-0 px-3 pb-2 pt-3">
                <div class="rounded-xl border border-zinc-800/90 bg-zinc-950/70 p-1 shadow-sm ring-1 ring-white/5">
                    <a
                        href="{{ BrightshellDomain::publicSiteUrl() }}"
                        class="flex items-center gap-3 rounded-lg px-2.5 py-2 transition hover:bg-zinc-800/50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-zinc-900/60 ring-1 ring-white/10">
                            <img
                                src="{{ BrightshellBrand::siteLogoUrl() }}"
                                alt="BrightShell"
                                width="36"
                                height="36"
                                class="h-full w-full object-contain p-1"
                                decoding="async"
                            >
                        </span>
                        <span class="min-w-0 flex-1 text-left">
                            <span class="block truncate text-xs font-semibold uppercase tracking-[0.14em] text-zinc-100 font-display">BrightShell</span>
                            <span class="block truncate text-[11px] text-zinc-500">← Retour au site</span>
                        </span>
                    </a>
                </div>
            </div>

            {{-- ┌───────────────────────────────────────────────────────── --}}
            {{-- │ Boîte 2 — seul parent de .portal-nav (pas de wrapper intermédiaire) --}}
            {{-- └───────────────────────────────────────────────────────── --}}
            <div
                class="portal-sidebar-box-2 portal-sidebar-box-nav flex min-h-0 min-w-0 flex-col overflow-hidden rounded-xl border border-zinc-800/80 bg-zinc-950/40 px-3 py-2 shadow-sm ring-1 ring-white/5"
            >
                    <nav
                        class="portal-nav flex min-h-0 min-w-0 flex-1 flex-col gap-0.5 overflow-y-auto overflow-x-hidden overscroll-y-contain px-2 py-2 [scrollbar-gutter:stable]"
                        aria-label="Navigation"
                    >
                        @php
                            $navLink = static function (string $route, string $label, string $icon, array $extra = []) use ($portalKey): array {
                                $href  = route($route, $extra);
                                $active = request()->routeIs($route);
                                return compact('href', 'label', 'icon', 'active');
                            };
                        @endphp

                        @php
                            $dashboardRouteActive = match ($portalKey) {
                                'home' => request()->routeIs('portals.home'),
                                'admin' => request()->routeIs('admin.dashboard'),
                                'settings' => request()->routeIs('portals.settings') && ! request()->routeIs('portals.settings.support-ticket.*'),
                                'docs' => request()->routeIs('portals.docs'),
                                'courses' => request()->routeIs('portals.courses'),
                                'collabs' => request()->routeIs('portals.collabs*'),
                                'users' => request()->routeIs('portals.users') && ! request()->routeIs('portals.users.companies.*'),
                                'project' => request()->routeIs('portals.project'),
                                default => request()->routeIs($portalKey.'.dashboard'),
                            };
                        @endphp
                        @include('layouts.partials.nav-item', [
                            'href'   => $accessiblePortals[$portalKey]['href'] ?? '#',
                            'active' => $dashboardRouteActive,
                            'label'  => 'Tableau de bord',
                            'icon'   => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                        ])

                        @if ($portalKey === 'settings')
                            @include('layouts.partials.nav-section', ['label' => 'Réglages'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.settings.profile.edit'),
                                'active' => request()->routeIs('portals.settings.profile.*'),
                                'label'  => 'Profil',
                                'icon'   => '<circle cx="12" cy="8" r="4"/><path d="M4 20v-1a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v1"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.settings.notifications.edit'),
                                'active' => request()->routeIs('portals.settings.notifications.*'),
                                'label'  => 'Notifications',
                                'icon'   => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.settings.security.edit'),
                                'active' => request()->routeIs('portals.settings.security.*'),
                                'label'  => 'Sécurité',
                                'icon'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.settings.support-ticket.create'),
                                'active' => request()->routeIs('portals.settings.support-ticket.*'),
                                'label'  => 'Demande & support',
                                'icon'   => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
                            ])
                            @if ($u && $u->hasRole('developer'))
                                @include('layouts.partials.nav-item', [
                                    'href'   => route('portals.settings.api.index'),
                                    'active' => request()->routeIs('portals.settings.api.*'),
                                    'label'  => 'API',
                                    'icon'   => '<path d="M9 3h6v6H9zM9 15h6v6H9zM4 9h16v6H4z"/>',
                                ])
                            @endif
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.settings.account.archive'),
                                'active' => request()->routeIs('portals.settings.account.*'),
                                'label'  => 'Compte',
                                'icon'   => '<path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/>',
                            ])
                        @endif

                        @if ($portalKey === 'docs')
                            @include('layouts.partials.nav-section', ['label' => 'Documentation'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.docs'),
                                'active' => request()->routeIs('portals.docs'),
                                'label'  => 'Sommaire',
                                'icon'   => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
                            ])
                            @include('portals.docs.partials.sidebar-tree')
                        @endif

                        @if ($portalKey === 'admin')
                            @include('layouts.partials.nav-section', ['label' => 'Membres'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.members.index'),
                                'active' => request()->routeIs('admin.members.*'),
                                'label'  => 'Membres',
                                'icon'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.clients.index'),
                                'active' => request()->routeIs('admin.clients.*'),
                                'label'  => 'Clients',
                                'icon'   => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.collaborators.index'),
                                'active' => request()->routeIs('admin.collaborators.index'),
                                'label'  => 'Collaborateurs',
                                'icon'   => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.collaborator-teams.index'),
                                'active' => request()->routeIs('admin.collaborator-teams.*'),
                                'label'  => 'Groupes collaborateurs',
                                'icon'   => '<path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.support-tickets.index'),
                                'active' => request()->routeIs('admin.support-tickets.*'),
                                'label'  => 'Tickets & demandes',
                                'icon'   => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.contact-messages.index'),
                                'active' => request()->routeIs('admin.contact-messages.*'),
                                'label'  => 'Messages de contact',
                                'icon'   => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                            ])

                            @include('layouts.partials.nav-section', ['label' => 'Formation'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.student-courses.index'),
                                'active' => request()->routeIs('admin.student-courses.*'),
                                'label'  => 'Cours élèves',
                                'icon'   => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8M8 11h8M8 15h4"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.student-subjects.index'),
                                'active' => request()->routeIs('admin.student-subjects.*'),
                                'label'  => 'Matières & dossiers',
                                'icon'   => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
                            ])

                            @include('layouts.partials.nav-section', ['label' => 'Sociétés'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.companies.index'),
                                'active' => request()->routeIs('admin.companies.*'),
                                'label'  => 'Sociétés',
                                'icon'   => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.projects.index'),
                                'active' => request()->routeIs('admin.projects.*'),
                                'label'  => 'Projets clients',
                                'icon'   => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.project-invitations.index'),
                                'active' => request()->routeIs('admin.project-invitations.*'),
                                'label'  => 'Invitations projets',
                                'icon'   => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                            ])

                            @include('layouts.partials.nav-section', ['label' => 'Finance'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.invoices.index'),
                                'active' => request()->routeIs('admin.invoices.*'),
                                'label'  => 'Factures',
                                'icon'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.declarations.index'),
                                'active' => request()->routeIs('admin.declarations.*') || request()->routeIs('admin.urssaf.*'),
                                'label'  => 'Déclarations',
                                'icon'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/>',
                            ])

                            @include('layouts.partials.nav-section', ['label' => 'Outils'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.audit-logs.index'),
                                'active' => request()->routeIs('admin.audit-logs.*'),
                                'label'  => 'Journal d’activité',
                                'icon'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.system-health'),
                                'active' => request()->routeIs('admin.system-health'),
                                'label'  => 'Santé technique',
                                'icon'   => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.search'),
                                'active' => request()->routeIs('admin.search'),
                                'label'  => 'Recherche',
                                'icon'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.api-manager.index'),
                                'active' => request()->routeIs('admin.api-manager.*'),
                                'label'  => 'API publique',
                                'icon'   => '<path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.outbound-api-widgets.index'),
                                'active' => request()->routeIs('admin.outbound-api-widgets.*'),
                                'label'  => 'API sortantes',
                                'icon'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.doc-nodes.index'),
                                'active' => request()->routeIs('admin.doc-nodes.*'),
                                'label'  => 'Documentation',
                                'icon'   => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8M8 11h6"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.mail-templates.index'),
                                'active' => request()->routeIs('admin.mail-templates.*'),
                                'label'  => 'Templates mail',
                                'icon'   => '<path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/>',
                            ])
                            @if (($mailWebUrl = BrightshellDomain::mailWebUrl()) !== '')
                                @include('layouts.partials.nav-item', [
                                    'href'     => $mailWebUrl,
                                    'active'   => false,
                                    'label'    => 'Webmail',
                                    'icon'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                                    'external' => true,
                                ])
                            @endif
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.site-appearance.edit'),
                                'active' => request()->routeIs('admin.site-appearance.*'),
                                'label'  => 'Identité & mails',
                                'icon'   => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.quesako-builder.edit'),
                                'active' => request()->routeIs('admin.quesako-builder.*'),
                                'label'  => 'Quesako Builder',
                                'icon'   => '<path d="M4 7h16M4 12h12M4 17h8"/><path d="M18 17l2 2 4-4"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.realisations.index'),
                                'active' => request()->routeIs('admin.realisations.*'),
                                'label'  => 'Réalisations',
                                'icon'   => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><path d="M7 8l3 3 5-5"/>',
                            ])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.cv.index'),
                                'active' => request()->routeIs('admin.cv.*'),
                                'label'  => 'Mon CV',
                                'icon'   => '<rect x="2" y="2" width="20" height="20" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
                            ])
                        @endif

                        @if ($portalKey === 'courses')
                            @include('layouts.partials.nav-section', ['label' => 'Ressources'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.courses.matieres.index'),
                                'active' => request()->routeIs('portals.courses.matieres.*'),
                                'label'  => 'Matières & fichiers',
                                'icon'   => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
                            ])
                        @endif

                        @if ($portalKey === 'collabs')
                            @include('layouts.partials.nav-section', ['label' => 'Équipes'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.collabs.teams.index'),
                                'active' => request()->routeIs('portals.collabs.teams.*'),
                                'label'  => 'Équipes & accès',
                                'icon'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                            ])
                        @endif

                        @if ($portalKey === 'users')
                            @include('layouts.partials.nav-section', ['label' => 'Entreprise'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.users.companies.index'),
                                'active' => request()->routeIs('portals.users.companies.*'),
                                'label'  => 'Mes sociétés',
                                'icon'   => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
                            ])
                        @endif

                        @if ($portalKey === 'project')
                            @include('layouts.partials.nav-section', ['label' => 'Projets'])
                            @can('create', \App\Models\Project::class)
                                @include('layouts.partials.nav-item', [
                                    'href'   => route('portals.project.create'),
                                    'active' => request()->routeIs('portals.project.create'),
                                    'label'  => 'Nouveau projet',
                                    'icon'   => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
                                ])
                            @endcan
                            @forelse ($sidebarProjects as $sidebarProject)
                                @include('layouts.partials.nav-item', [
                                    'href'   => route('portals.project.show', $sidebarProject),
                                    'active' => request()->routeIs('portals.project.show') && request()->route('project')?->is($sidebarProject),
                                    'label'  => $sidebarProject->name,
                                    'icon'   => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
                                ])
                            @empty
                                <p class="px-3 py-2 text-xs text-zinc-500">Aucun projet accessible pour l’instant.</p>
                            @endforelse
                            @if ($sidebarProjects->count() >= 25)
                                <p class="px-3 pb-1 text-[11px] leading-snug text-zinc-500">Affichage limité à 25 projets — voir le tableau de bord pour l’ensemble.</p>
                            @endif
                            @include('layouts.partials.nav-section', ['label' => 'Portail'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('portals.project.settings'),
                                'active' => request()->routeIs('portals.project.settings'),
                                'label'  => 'Paramètres',
                                'icon'   => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
                            ])
                        @endif

                        @hasSection('sidebar_nav')
                            <div class="mt-2 flex flex-col gap-0.5 border-t border-zinc-800/70 pt-2">
                                @yield('sidebar_nav')
                            </div>
                        @endif
                    </nav>
            </div>

            {{-- ┌───────────────────────────────────────────────────────── --}}
            {{-- │ Boîte 3 — Compte (bas, shrink-0, pas de mt-auto nécessaire) --}}
            {{-- └───────────────────────────────────────────────────────── --}}
            @if ($u)
                <div class="portal-sidebar-box-account shrink-0 border-t border-zinc-800/90 bg-zinc-900/95 px-3 pb-3 pt-3">
                    <div class="space-y-2 rounded-xl border border-zinc-800/90 bg-zinc-950/70 p-2 shadow-sm ring-1 ring-white/5">
                        <div class="flex items-center gap-3 rounded-lg border border-zinc-800/60 bg-zinc-900/40 px-2.5 py-2">
                            @include('partials.user-avatar', ['user' => $u])
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-100">{{ $u->name }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $u->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-800/40 px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider text-zinc-400 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500"
                            >
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </aside>
        @endunless

        {{-- ===================== CONTENU ===================== --}}
        <div class="portal-wrap relative flex min-h-screen min-w-0 flex-1 flex-col">

            {{-- Topbar --}}
            <header class="portal-topbar sticky top-0 z-20 flex min-w-0 shrink-0 flex-wrap items-center gap-x-2 gap-y-2 border-b border-zinc-800 bg-zinc-950/80 px-3 py-2.5 backdrop-blur-md sm:flex-nowrap sm:gap-x-3 sm:gap-y-0 sm:px-4 sm:py-3">

                @unless ($portalKey === 'home')
                {{-- Burger mobile --}}
                <button
                    type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-900 text-zinc-200 transition hover:border-zinc-600 lg:hidden focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                    data-portal-menu-toggle
                    aria-label="Ouvrir le menu"
                    aria-expanded="false"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @endunless

                {{-- Label page --}}
                <span class="max-w-[8rem] truncate text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-400 font-display sm:max-w-none sm:text-xs lg:block">@yield('topbar_label', 'Tableau de bord')</span>

                @if ($portalKey === 'home' && $u)
                    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                        @include('partials.user-avatar', ['user' => $u])
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-lg border border-zinc-700 bg-zinc-900 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-zinc-400 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500"
                            >
                                Déconnexion
                            </button>
                        </form>
                    </div>
                @endif

                <div class="min-w-0 flex-1" aria-hidden="true"></div>

                @if ($portalKey === 'admin')
                    <form
                        method="GET"
                        action="{{ route('admin.search') }}"
                        class="hidden min-w-0 max-w-[min(100vw-12rem,18rem)] shrink sm:flex md:max-w-xs"
                        role="search"
                        aria-label="Recherche administration"
                    >
                        <label for="admin-topbar-search-q" class="sr-only">Rechercher membres, sociétés, tickets</label>
                        <div class="flex h-9 w-full min-w-0 items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900/80 px-2.5 py-0 text-sm leading-none text-zinc-200 ring-1 ring-transparent transition focus-within:border-indigo-500/50 focus-within:ring-indigo-500/25">
                            <svg class="h-4 w-4 shrink-0 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input
                                id="admin-topbar-search-q"
                                type="search"
                                name="q"
                                value="{{ request()->routeIs('admin.search') ? request('q') : '' }}"
                                placeholder="Rechercher…"
                                autocomplete="off"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-zinc-200 placeholder:text-zinc-500 focus:outline-none focus:ring-0"
                            >
                        </div>
                    </form>
                    <a
                        href="{{ route('admin.search') }}"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-900/80 text-zinc-300 transition hover:border-indigo-500/50 hover:text-indigo-300 sm:hidden"
                        aria-label="Recherche administration"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </a>
                @endif

                {{-- Slot extra --}}
                <div class="order-3 -mx-1 flex w-full min-w-0 items-center gap-2 overflow-x-auto px-1 pb-0.5 sm:order-none sm:mx-0 sm:w-auto sm:overflow-visible sm:px-0 sm:pb-0">
                    @stack('topbar_extra')
                </div>

                {{-- ↓ Sélecteur de portail --}}
                @if (count($accessiblePortals) > 1)
                    <div class="relative shrink-0" data-portal-switcher>
                        <button
                            type="button"
                            class="flex h-9 shrink-0 items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900/80 px-3 py-0 text-sm font-semibold leading-none text-zinc-200 transition hover:border-indigo-500/50 hover:bg-zinc-800/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                            data-portal-switcher-btn
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="Changer de portail"
                        >
                            @include('layouts.partials.portal-icon-mark', ['key' => $portalKey, 'frame' => 'xs'])
                            <span class="hidden max-w-[10rem] truncate sm:inline">{{ $currentPortalLabel }}</span>
                            <svg class="h-4 w-4 shrink-0 opacity-50 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" data-portal-switcher-chevron><path d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <div
                            class="absolute right-0 top-[calc(100%+0.5rem)] z-50 hidden min-w-[14rem] overflow-hidden rounded-xl border border-zinc-700 bg-zinc-900 shadow-2xl shadow-black/50 ring-1 ring-white/5"
                            data-portal-switcher-menu
                            role="menu"
                        >
                            <p class="border-b border-zinc-800 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-500">Mes portails</p>
                            <ul class="space-y-0.5 p-1.5">
                                @foreach ($accessiblePortals as $key => $portal)
                                    <li role="none">
                                        <a
                                            href="{{ $portal['href'] }}"
                                            role="menuitem"
                                            @class([
                                                'flex h-9 items-center gap-2 rounded-lg px-3 py-0 text-sm leading-none transition',
                                                'bg-indigo-500/10 text-white ring-1 ring-inset ring-indigo-500/20' => $key === $portalKey,
                                                'text-zinc-300 hover:bg-zinc-800 hover:text-white' => $key !== $portalKey,
                                            ])
                                        >
                                            @include('layouts.partials.portal-icon-mark', ['key' => $key, 'frame' => 'xs'])
                                            <span class="min-w-0 flex-1">{{ $portal['label'] }}</span>
                                            @if ($key === $portalKey)
                                                <svg class="h-4 w-4 shrink-0 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Indicateur session --}}
                <span class="hidden items-center gap-2 text-xs font-medium text-zinc-500 sm:inline-flex">
                    <span class="relative flex h-2 w-2" aria-hidden="true">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-40"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-emerald-500/30"></span>
                    </span>
                    Session active
                </span>
            </header>

            <main class="portal-main portal-main-scale mx-auto w-full min-w-0 flex-1 px-4 pb-6 sm:px-6 lg:px-8 lg:pb-8 @yield('portal_main_max', 'max-w-7xl') @yield('portal_main_class')">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    // Mesure la hauteur du topbar et expose --topbar-h pour le padding-top de .portal-main
    (() => {
        const header = document.querySelector('.portal-topbar');
        if (!header) return;
        const update = () => document.documentElement.style.setProperty('--topbar-h', header.offsetHeight + 'px');
        update();
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(update).observe(header);
        }
    })();

    // Portal switcher toggle
    (() => {
        const switcher = document.querySelector('[data-portal-switcher]');
        if (!switcher) return;
        const btn   = switcher.querySelector('[data-portal-switcher-btn]');
        const menu  = switcher.querySelector('[data-portal-switcher-menu]');
        const chev  = switcher.querySelector('[data-portal-switcher-chevron]');
        const viewportPadding = 8;
        const isMobileSidebar = () => window.matchMedia('(max-width: 1023px)').matches;

        const positionMenu = () => {
            if (menu.classList.contains('hidden')) return;

            if (!isMobileSidebar()) {
                // Desktop: comportement natif (ancrage CSS right-0), sans calcul JS.
                menu.style.removeProperty('position');
                menu.style.removeProperty('left');
                menu.style.removeProperty('top');
                menu.style.removeProperty('right');
                menu.style.removeProperty('width');
                menu.style.removeProperty('maxWidth');
                return;
            }

            // Menu en fixed pour rester borné au viewport, quel que soit le wrap topbar.
            menu.style.position = 'fixed';
            menu.style.right = 'auto';
            const viewportWidth = Math.max(0, document.documentElement.clientWidth || window.innerWidth || 0);
            const viewportHeight = Math.max(0, document.documentElement.clientHeight || window.innerHeight || 0);
            menu.style.maxWidth = `${Math.max(160, viewportWidth - viewportPadding * 2)}px`;

            const btnRect = btn.getBoundingClientRect();
            const firstRect = menu.getBoundingClientRect();

            // Si la largeur intrinsèque dépasse l’écran, on force la largeur dispo.
            if (firstRect.width > viewportWidth - viewportPadding * 2) {
                menu.style.width = `${Math.max(160, viewportWidth - viewportPadding * 2)}px`;
            } else {
                menu.style.removeProperty('width');
            }

            const computeLeft = (menuWidth) => {
                // Alignement desktop/mobile stable: bord droit du menu sur bord droit du bouton,
                // puis clamp viewport pour ne jamais sortir de l'écran.
                const idealLeft = (btnRect.right - menuWidth) - 4;
                const maxLeft = Math.max(viewportPadding, viewportWidth - menuWidth - viewportPadding);
                return Math.min(maxLeft, Math.max(viewportPadding, idealLeft));
            };

            const rect = menu.getBoundingClientRect();
            const left = computeLeft(rect.width);

            let top = btnRect.bottom + 8;
            if (top + rect.height > viewportHeight - viewportPadding) {
                top = Math.max(viewportPadding, viewportHeight - rect.height - viewportPadding);
            }

            menu.style.left = `${left}px`;
            menu.style.top = `${top}px`;

            // Recalcul complet après paint: largeur finale -> alignement final stable.
            requestAnimationFrame(() => {
                if (menu.classList.contains('hidden')) return;
                const fixedRect = menu.getBoundingClientRect();
                menu.style.left = `${computeLeft(fixedRect.width)}px`;
            });
        };

        const open  = () => {
            menu.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
            chev.classList.add('rotate-180');
            positionMenu();
        };
        const close = () => {
            menu.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            chev.classList.remove('rotate-180');
            menu.style.removeProperty('position');
            menu.style.removeProperty('left');
            menu.style.removeProperty('top');
            menu.style.removeProperty('right');
            menu.style.removeProperty('width');
            menu.style.removeProperty('maxWidth');
        };

        btn.addEventListener('click', (e) => { e.stopPropagation(); menu.classList.contains('hidden') ? open() : close(); });
        document.addEventListener('click', close);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
        window.addEventListener('resize', positionMenu);
        window.addEventListener('scroll', () => {
            if (isMobileSidebar()) {
                positionMenu();
            }
        }, { passive: true });
    })();
    </script>

    @php
        $bsRootDomain = \App\Support\BrightshellDomain::effectiveRoot();
        $bsNotifBridgeUrl = route('portals.settings.notifications.bridge');
    @endphp
    <iframe
        id="bs-notif-bridge-frame"
        src="{{ $bsNotifBridgeUrl }}"
        title="BrightShell Notification Bridge"
        tabindex="-1"
        aria-hidden="true"
        style="position:absolute;width:0;height:0;border:0;opacity:0;pointer-events:none;"
    ></iframe>
    <script>
        (function () {
            const bridgeFrame = document.getElementById('bs-notif-bridge-frame');
            const bridgeUrl = @json($bsNotifBridgeUrl);
            const bridgeOrigin = (() => {
                try { return new URL(bridgeUrl).origin; } catch (_) { return null; }
            })();
            const currentOrigin = window.location.origin;
            const rootDomain = @json($bsRootDomain);

            const allowedHost = (host) => {
                if (!rootDomain || !host) return false;
                return host === rootDomain || host.endsWith('.' + rootDomain);
            };

            const callBridge = (action, payload = {}) => new Promise((resolve, reject) => {
                if (!bridgeFrame || !bridgeOrigin || !bridgeFrame.contentWindow) {
                    reject(new Error('bridge_unavailable'));
                    return;
                }

                const requestId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
                const timer = window.setTimeout(() => {
                    window.removeEventListener('message', onMessage);
                    reject(new Error('bridge_timeout'));
                }, 5000);

                const onMessage = (event) => {
                    const data = event?.data;
                    if (event.origin !== bridgeOrigin || !data || data.__bsNotifBridge !== true || data.requestId !== requestId) {
                        return;
                    }
                    window.clearTimeout(timer);
                    window.removeEventListener('message', onMessage);
                    if (data.ok) {
                        resolve(data);
                    } else {
                        reject(Object.assign(new Error(data.error || 'bridge_error'), { data }));
                    }
                };

                window.addEventListener('message', onMessage);
                bridgeFrame.contentWindow.postMessage({
                    __bsNotifBridge: true,
                    requestId,
                    action,
                    ...payload,
                }, bridgeOrigin);
            });

            const api = {
                async getPermission() {
                    try {
                        const res = await callBridge('status');
                        return res.permission || 'default';
                    } catch (_) {
                        return ('Notification' in window) ? Notification.permission : 'unsupported';
                    }
                },
                async requestPermission() {
                    // La demande de permission doit se faire sur la page top-level de l'origine settings.
                    if (currentOrigin === bridgeOrigin) {
                        if (!('Notification' in window)) {
                            throw new Error('unsupported');
                        }
                        const permission = await Notification.requestPermission();
                        return { ok: true, permission };
                    }

                    const err = new Error('request_permission_requires_settings_origin');
                    err.data = {
                        code: 'request_permission_requires_settings_origin',
                        settingsUrl: bridgeUrl.replace(/\/notifications\/bridge$/, '/notifications'),
                    };
                    throw err;
                },
                notify(title, options = {}) {
                    return callBridge('notify', { title, options });
                },
                canBridgeFromCurrentHost() {
                    return allowedHost(window.location.hostname);
                },
                bridgeOrigin: bridgeOrigin,
                bridgeUrl: bridgeUrl,
            };

            window.BrightshellNotifications = api;
        })();
    </script>

    @stack('scripts')
</body>
</html>
