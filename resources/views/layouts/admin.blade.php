@php
    use App\Support\PortalUrls;
    use Illuminate\Support\Str;

    $host = strtolower((string) request()->getHost());
    $labels = explode('.', $host);
    $portalKey = 'admin';
    if (count($labels) >= 3) {
        $portalKey = match ($labels[0]) {
            'admin', 'collabs', 'users', 'courses', 'settings' => $labels[0],
            default => 'admin',
        };
    } elseif (count($labels) === 2 && str_ends_with($host, '.localhost')) {
        $portalKey = match ($labels[0]) {
            'admin', 'collabs', 'users', 'courses', 'settings' => $labels[0],
            default => 'admin',
        };
    }

    $u = auth()->user();

    // Portails accessibles selon les rôles de l'utilisateur
    $allPortals = [
        'admin'    => ['label' => 'Administration',  'href' => PortalUrls::forRoleSlug('admin'),        'role' => 'admin'],
        'collabs'  => ['label' => 'Collaborateurs',  'href' => PortalUrls::forRoleSlug('collaborator'), 'role' => 'collaborator'],
        'users'    => ['label' => 'Espace client',   'href' => PortalUrls::forRoleSlug('client'),       'role' => 'client'],
        'courses'  => ['label' => 'Cours',           'href' => PortalUrls::forRoleSlug('student'),      'role' => 'student'],
        'settings' => ['label' => 'Réglages',        'href' => PortalUrls::settingsUrl(),               'role' => null],
    ];

    $isAdmin = $u && ($u->isAdmin() || $u->hasRole('admin'));

    $accessiblePortals = collect($allPortals)->filter(function ($portal) use ($u, $isAdmin) {
        if ($portal['role'] === null) return true; // settings = tout le monde
        if ($isAdmin) return true;
        return $u && $u->hasRole($portal['role']);
    })->all();

    $portalIcons = [
        'admin'    => '<path d="M12 3l7 4v10l-7 4-7-4V7l7-4z" stroke-linejoin="round"/><path d="M12 12l7-4M12 12v10M12 12L5 8"/>',
        'collabs'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'users'    => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
        'courses'  => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
    ];

    $currentPortalLabel = $allPortals[$portalKey]['label'] ?? 'Portail';
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portail') — BrightShell</title>
    <link rel="icon" href="{{ asset('img/etoile_sans_fond_contours_fin.png') }}" type="image/png">
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
    @stack('styles')
</head>
<body class="auth-body min-h-full bg-zinc-950 text-zinc-100 antialiased">
    <div class="pointer-events-none fixed inset-0 z-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(99,102,241,0.12),transparent)]" aria-hidden="true"></div>

    <div
        class="portal-shell group relative z-10 flex min-h-screen w-full items-start"
        data-portal="{{ $portalKey }}"
    >
        {{-- Backdrop mobile --}}
        <div
            class="portal-sidebar-backdrop fixed inset-0 z-[35] hidden bg-black/60 backdrop-blur-sm group-[.portal-sidebar-open]:block lg:!hidden"
            data-portal-sidebar-backdrop
            aria-hidden="true"
        ></div>

        {{-- ===================== SIDEBAR ===================== --}}
        <aside
            class="portal-sidebar fixed inset-y-0 left-0 z-40 grid h-dvh min-h-0 w-[min(18.5rem,88vw)] max-w-full shrink-0 -translate-x-full grid-rows-[auto_minmax(0,1fr)_auto] overflow-x-hidden overflow-y-hidden border-r border-zinc-800 bg-zinc-900/95 backdrop-blur-xl transition-transform duration-300 group-[.portal-sidebar-open]:translate-x-0 lg:sticky lg:top-0 lg:z-auto lg:h-dvh lg:w-64 lg:max-w-none lg:translate-x-0"
            aria-label="Navigation {{ $currentPortalLabel }}"
        >
            {{-- ┌───────────────────────────────────────────────────────── --}}
            {{-- │ Boîte 1 — Marque (hauteur contenu, jamais écrasée)          --}}
            {{-- └───────────────────────────────────────────────────────── --}}
            <div class="portal-sidebar-box-brand relative z-10 shrink-0 px-3 pb-2 pt-3">
                <div class="rounded-xl border border-zinc-800/90 bg-zinc-950/70 p-1 shadow-sm ring-1 ring-white/5">
                    <a
                        href="{{ url('/') }}"
                        class="flex items-center gap-3 rounded-lg px-2.5 py-2 transition hover:bg-zinc-800/50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-500/15 text-xs font-bold tracking-wider text-indigo-400 ring-1 ring-indigo-500/30 font-display">BS</span>
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

                        @include('layouts.partials.nav-item', [
                            'href'   => $accessiblePortals[$portalKey]['href'] ?? '#',
                            'active' => request()->routeIs($portalKey.'.dashboard'),
                            'label'  => 'Tableau de bord',
                            'icon'   => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                        ])

                        @if ($portalKey === 'admin')
                            @include('layouts.partials.nav-section', ['label' => 'Membres'])
                            @include('layouts.partials.nav-item', [
                                'href'   => route('admin.members.index'),
                                'active' => request()->routeIs('admin.members.*'),
                                'label'  => 'Membres',
                                'icon'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
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
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white font-display"
                                aria-hidden="true"
                            >{{ Str::upper(Str::substr(trim($u->name), 0, 1)) }}</div>
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

        {{-- ===================== CONTENU ===================== --}}
        <div class="portal-wrap flex min-h-screen min-w-0 flex-1 flex-col">

            {{-- Topbar --}}
            <header class="portal-topbar sticky top-0 z-20 flex shrink-0 items-center gap-3 border-b border-zinc-800 bg-zinc-950/80 px-4 py-3 backdrop-blur-md">

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

                {{-- Label page --}}
                <span class="truncate text-xs font-semibold uppercase tracking-[0.12em] text-zinc-400 font-display lg:block">@yield('topbar_label', 'Tableau de bord')</span>

                <div class="min-w-0 flex-1" aria-hidden="true"></div>

                {{-- Slot extra --}}
                <div class="flex shrink-0 items-center gap-2">@stack('topbar_extra')</div>

                {{-- ↓ Sélecteur de portail --}}
                @if (count($accessiblePortals) > 1)
                    <div class="relative" data-portal-switcher>
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-xs font-semibold text-zinc-200 transition hover:border-indigo-500/50 hover:bg-zinc-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                            data-portal-switcher-btn
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="Changer de portail"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                {!! $portalIcons[$portalKey] ?? $portalIcons['settings'] !!}
                            </svg>
                            <span class="hidden sm:inline">{{ $currentPortalLabel }}</span>
                            <svg class="h-3.5 w-3.5 shrink-0 opacity-50 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" data-portal-switcher-chevron><path d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <div
                            class="absolute right-0 top-[calc(100%+0.5rem)] z-50 hidden min-w-[14rem] overflow-hidden rounded-xl border border-zinc-700 bg-zinc-900 shadow-2xl shadow-black/50 ring-1 ring-white/5"
                            data-portal-switcher-menu
                            role="menu"
                        >
                            <p class="border-b border-zinc-800 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-500">Mes portails</p>
                            <ul class="p-1.5 space-y-0.5">
                                @foreach ($accessiblePortals as $key => $portal)
                                    <li role="none">
                                        <a
                                            href="{{ $portal['href'] }}"
                                            role="menuitem"
                                            @class([
                                                'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition',
                                                'bg-indigo-500/10 text-white ring-1 ring-inset ring-indigo-500/20' => $key === $portalKey,
                                                'text-zinc-300 hover:bg-zinc-800 hover:text-white' => $key !== $portalKey,
                                            ])
                                        >
                                            <svg class="h-4 w-4 shrink-0 opacity-75" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                {!! $portalIcons[$key] ?? $portalIcons['settings'] !!}
                                            </svg>
                                            <span class="flex-1">{{ $portal['label'] }}</span>
                                            @if ($key === $portalKey)
                                                <svg class="h-3.5 w-3.5 shrink-0 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
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

            <main class="portal-main mx-auto w-full flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8 @yield('portal_main_max', 'max-w-7xl')">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    // Portal switcher toggle
    (() => {
        const switcher = document.querySelector('[data-portal-switcher]');
        if (!switcher) return;
        const btn   = switcher.querySelector('[data-portal-switcher-btn]');
        const menu  = switcher.querySelector('[data-portal-switcher-menu]');
        const chev  = switcher.querySelector('[data-portal-switcher-chevron]');

        const open  = () => { menu.classList.remove('hidden'); btn.setAttribute('aria-expanded', 'true');  chev.classList.add('rotate-180'); };
        const close = () => { menu.classList.add('hidden');    btn.setAttribute('aria-expanded', 'false'); chev.classList.remove('rotate-180'); };

        btn.addEventListener('click', (e) => { e.stopPropagation(); menu.classList.contains('hidden') ? open() : close(); });
        document.addEventListener('click', close);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    })();
    </script>

    @stack('scripts')
</body>
</html>
