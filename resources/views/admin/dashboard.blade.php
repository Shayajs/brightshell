@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('topbar_label', 'Tableau de bord')

@push('topbar_extra')
    <div
        class="hidden items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900/80 px-3 py-2 text-sm text-zinc-500 sm:flex"
        role="search"
        aria-label="Recherche à venir"
    >
        <svg class="h-4 w-4 shrink-0 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <span class="truncate">Rechercher…</span>
    </div>
@endpush

@section('content')
    <div class="space-y-8">
        {{-- En-tête --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Bonjour, {{ \Illuminate\Support\Str::before(trim($user->name), ' ') ?: $user->name }}
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-zinc-400">
                    Pilotage BrightShell : métriques, activité et listes — interface en Tailwind, prête à brancher sur tes données.
                </p>
            </div>
            <div class="flex flex-wrap gap-1.5 rounded-xl border border-zinc-800 bg-zinc-900/60 p-1" role="group" aria-label="Période" id="period-tabs">
                <button type="button" data-period="30j"
                    class="period-tab rounded-lg px-3.5 py-1.5 text-xs font-semibold transition
                           bg-indigo-600 text-white shadow-sm"
                    aria-pressed="true">30 jours</button>
                <button type="button" data-period="3m"
                    class="period-tab rounded-lg px-3.5 py-1.5 text-xs font-semibold transition
                           text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800"
                    aria-pressed="false">3 mois</button>
                <button type="button" data-period="1y"
                    class="period-tab rounded-lg px-3.5 py-1.5 text-xs font-semibold transition
                           text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800"
                    aria-pressed="false">Année</button>
            </div>
        </div>

        {{-- KPIs --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicateurs clés">
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Chiffre d’affaires</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/25" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold text-white">—</p>
                <p class="mt-1 text-xs font-medium text-emerald-400/90">+0 % vs période précédente</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Projets actifs</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/25" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-xs text-zinc-500">Module projets à connecter</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Clients</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/25" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-xs text-zinc-500">CRM &amp; migration Allotata</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Environnement</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/25" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold uppercase text-white">{{ config('app.env') }}</p>
                <p class="mt-1 truncate text-xs text-zinc-500" title="{{ config('app.url') }}">{{ config('app.url') }}</p>
            </article>
        </section>

        {{-- Deux colonnes --}}
        <div class="grid gap-4 lg:grid-cols-5 lg:gap-6">
            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/60 ring-1 ring-white/5 lg:col-span-3">
                <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Aperçu d’activité</h2>
                    <span class="text-xs font-semibold text-indigo-400">Démo</span>
                </div>
                <div class="p-5">
                    <p class="mb-4 text-xs text-zinc-500">Données fictives — remplaçables par ApexCharts ou équivalent.</p>
                    <div class="flex h-36 items-end justify-between gap-1 sm:gap-2" role="img" aria-label="Histogramme" id="activity-chart">
                        {{-- Barres générées dynamiquement par JS --}}
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-[10px] text-zinc-600" id="chart-period-label">7 derniers jours</p>
                        <p class="text-[10px] text-zinc-600">Données démo</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/60 ring-1 ring-white/5 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Activité récente</h2>
                </div>
                <ul class="divide-y divide-zinc-800/80 p-2">
                    <li class="flex gap-3 px-3 py-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500 ring-4 ring-indigo-500/20" aria-hidden="true"></span>
                        <div>
                            <p class="text-sm font-medium text-zinc-200">Connexion administrateur</p>
                            <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                        </div>
                    </li>
                    <li class="flex gap-3 px-3 py-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500 ring-4 ring-indigo-500/20" aria-hidden="true"></span>
                        <div>
                            <p class="text-sm font-medium text-zinc-200">Portails multi-sous-domaines</p>
                            <p class="text-xs text-zinc-500">Admin, collabs, clients, cours, réglages</p>
                        </div>
                    </li>
                    <li class="flex gap-3 px-3 py-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500 ring-4 ring-indigo-500/20" aria-hidden="true"></span>
                        <div>
                            <p class="text-sm font-medium text-zinc-200">Prochaine étape</p>
                            <p class="text-xs text-zinc-500">Modèles &amp; écrans métier</p>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        {{-- Tableau --}}
        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/60 ring-1 ring-white/5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Projets (exemple)</h2>
                <span class="rounded-md border border-zinc-700 bg-zinc-950 px-2 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Démo UI</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[32rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                            <th class="px-5 py-3">Projet</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3">Échéance</th>
                            <th class="px-5 py-3">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80 text-zinc-400">
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5 font-medium text-zinc-200">Site vitrine BrightShell</td>
                            <td class="px-5 py-3.5"><span class="inline-flex rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-400">En ligne</span></td>
                            <td class="px-5 py-3.5">—</td>
                            <td class="px-5 py-3.5">—</td>
                        </tr>
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5 font-medium text-zinc-200">Console admin</td>
                            <td class="px-5 py-3.5"><span class="inline-flex rounded-md border border-indigo-500/30 bg-indigo-500/10 px-2 py-0.5 text-xs font-semibold text-indigo-300">En cours</span></td>
                            <td class="px-5 py-3.5">—</td>
                            <td class="px-5 py-3.5">—</td>
                        </tr>
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5 font-medium text-zinc-200">Migration Allotata</td>
                            <td class="px-5 py-3.5"><span class="inline-flex rounded-md border border-zinc-600 bg-zinc-800/50 px-2 py-0.5 text-xs font-semibold text-zinc-400">Planifié</span></td>
                            <td class="px-5 py-3.5">—</td>
                            <td class="px-5 py-3.5">—</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
// ── Données démo par période ──────────────────────────────────────
const CHART_DATA = {
    '30j': {
        label: '30 derniers jours',
        bars: [
            { h: 38, l: 'S1' }, { h: 62, l: 'S2' }, { h: 48, l: 'S3' }, { h: 78, l: 'S4' },
        ],
    },
    '3m': {
        label: '3 derniers mois',
        bars: [
            { h: 55, l: 'Jan' }, { h: 72, l: 'Fév' }, { h: 88, l: 'Mar' },
        ],
    },
    '1y': {
        label: 'Année en cours',
        bars: [
            { h: 30, l: 'J' }, { h: 45, l: 'F' }, { h: 52, l: 'M' }, { h: 68, l: 'A' },
            { h: 74, l: 'M' }, { h: 55, l: 'J' }, { h: 60, l: 'J' }, { h: 78, l: 'A' },
            { h: 82, l: 'S' }, { h: 88, l: 'O' }, { h: 64, l: 'N' }, { h: 90, l: 'D' },
        ],
    },
};

// Période par défaut : 30j mais on simule les 7 derniers jours
CHART_DATA['30j'] = {
    label: '7 derniers jours',
    bars: [
        { h: 38, l: 'L' }, { h: 62, l: 'M' }, { h: 48, l: 'M' }, { h: 78, l: 'J' },
        { h: 52, l: 'V' }, { h: 88, l: 'S' }, { h: 68, l: 'D' },
    ],
};

function renderChart(period) {
    const chart  = document.getElementById('activity-chart');
    const lbl    = document.getElementById('chart-period-label');
    const data   = CHART_DATA[period] || CHART_DATA['30j'];

    lbl.textContent = data.label;

    chart.innerHTML = data.bars.map(({ h, l }) => `
        <div class="flex h-full min-h-0 flex-1 flex-col items-stretch justify-end gap-1.5">
            <div
                class="w-full rounded-t-md bg-gradient-to-t from-indigo-600 to-indigo-400 opacity-90 transition-all duration-500 hover:opacity-100"
                style="min-height:4px;height:${h}%;"
                title="${h} %"
            ></div>
            <span class="text-center text-[10px] font-medium uppercase text-zinc-600">${l}</span>
        </div>
    `).join('');
}

// ── Tabs période ─────────────────────────────────────────────────
const tabs = document.querySelectorAll('.period-tab');
let activePeriod = '30j';

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        activePeriod = tab.dataset.period;

        tabs.forEach(t => {
            const isActive = t === tab;
            t.setAttribute('aria-pressed', String(isActive));
            t.className = t.className
                .replace(/bg-indigo-600\s*text-white\s*shadow-sm/, '')
                .replace(/text-zinc-400\s*hover:text-zinc-200\s*hover:bg-zinc-800/, '')
                .trim();
            if (isActive) {
                t.classList.add('bg-indigo-600', 'text-white', 'shadow-sm');
            } else {
                t.classList.add('text-zinc-400', 'hover:text-zinc-200', 'hover:bg-zinc-800');
            }
        });

        renderChart(activePeriod);
    });
});

// Init
renderChart(activePeriod);
</script>
@endpush
