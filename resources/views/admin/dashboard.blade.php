@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('topbar_label', 'Tableau de bord')

@php
    $p30 = $dashboard['periods']['30j'];
    $k0 = $p30['kpis'];
@endphp

@push('topbar_extra')
    <div
        class="hidden h-9 shrink-0 items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900/80 px-3 py-0 text-sm leading-none text-zinc-500 sm:flex"
        role="search"
        aria-label="Recherche à venir"
    >
        <svg class="h-4 w-4 shrink-0 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <span class="truncate">Rechercher…</span>
    </div>
@endpush

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Bonjour, {{ $user->greetingFirstName() ?: $user->name }}
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-zinc-400">
                    Indicateurs issus de la base : sessions, inscriptions, factures payées et activité quiz.
                </p>
                @if (! $dashboard['uses_database_sessions'])
                    <p class="mt-2 max-w-xl rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-200/90">
                        <strong class="font-semibold">Sessions hors base</strong> — le graphique « membres actifs distincts » reste à 0.
                        Mettez <code class="rounded bg-zinc-950 px-1 py-0.5 text-[10px]">SESSION_DRIVER=database</code> pour l’historique réel.
                    </p>
                @endif
            </div>
            <div class="flex w-full flex-shrink-0 flex-wrap gap-1.5 rounded-xl border border-zinc-800 bg-zinc-900/60 p-1 sm:w-auto" role="group" aria-label="Période" id="period-tabs">
                <button type="button" data-period="30j"
                    class="period-tab rounded-lg px-3.5 py-1.5 text-xs font-semibold transition bg-indigo-600 text-white shadow-sm"
                    aria-pressed="true">30 jours</button>
                <button type="button" data-period="3m"
                    class="period-tab rounded-lg px-3.5 py-1.5 text-xs font-semibold transition text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800"
                    aria-pressed="false">3 mois</button>
                <button type="button" data-period="1y"
                    class="period-tab rounded-lg px-3.5 py-1.5 text-xs font-semibold transition text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800"
                    aria-pressed="false">Année</button>
            </div>
        </div>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-4" aria-label="Indicateurs clés">
            <article class="flex min-h-0 min-w-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">CA encaissé (période)</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/25" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold text-white" data-kpi="revenue_ttc">{{ number_format($k0['revenue_ttc'], 2, ',', ' ') }} €</p>
                <p class="mt-1 text-xs font-medium text-emerald-400/90" data-kpi="revenue_delta">
                    @if ($k0['revenue_delta_pct'] !== null)
                        {{ $k0['revenue_delta_pct'] >= 0 ? '+' : '' }}{{ $k0['revenue_delta_pct'] }} % vs période précédente
                    @else
                        — vs période précédente
                    @endif
                </p>
            </article>
            <article class="flex min-h-0 min-w-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Cours suivis</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/25" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold text-white" data-kpi="active_courses">{{ $k0['active_courses'] }}</p>
                <p class="mt-1 text-xs text-zinc-500"><span data-kpi="completed_courses">{{ $k0['completed_courses'] }}</span> terminés au total</p>
            </article>
            <article class="flex min-h-0 min-w-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Comptes</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/25" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-display text-2xl font-bold text-white" data-kpi="users_total">{{ $k0['users_total'] }}</p>
                <p class="mt-1 text-xs text-zinc-500"><span data-kpi="portal_users">{{ $k0['portal_users'] }}</span> client·s / élève·s (rôles)</p>
                <p class="mt-1 text-[11px] font-medium text-indigo-300/90"><span data-kpi="new_users_period">{{ $k0['new_users_period'] }}</span> nouvelle·aux inscriptions sur la période sélectionnée</p>
            </article>
            <article class="flex min-h-0 min-w-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
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

        <section class="space-y-4" aria-labelledby="admin-dashboard-charts-heading">
            <div class="flex flex-col gap-1 border-b border-zinc-800/80 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="admin-dashboard-charts-heading" class="font-display text-lg font-bold tracking-tight text-white">
                        Tendances du site
                    </h2>
                    <p class="mt-1 max-w-2xl text-sm text-zinc-500">
                        Même période que les boutons ci-dessus — les barres sont normalisées sur le pic de la plage affichée.
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-5 lg:grid-cols-2">
                @foreach (['visitors' => 'from-indigo-600 to-indigo-400', 'signups' => 'from-emerald-600 to-emerald-400', 'revenue' => 'from-amber-600 to-amber-400', 'quiz' => 'from-violet-600 to-violet-400'] as $chartKey => $gradient)
                    <article
                        class="flex min-h-0 min-w-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900/60 p-5 shadow-sm ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex min-w-0 gap-3">
                                <span
                                    class="mt-0.5 h-10 w-1 shrink-0 rounded-full bg-gradient-to-b {{ $gradient }} opacity-90 ring-1 ring-white/10"
                                    aria-hidden="true"
                                ></span>
                                <div class="min-w-0">
                                    <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white" data-chart-title="{{ $chartKey }}">{{ $p30['charts'][$chartKey]['title'] }}</h3>
                                    <p class="mt-1 max-w-md text-[11px] leading-snug text-zinc-500" data-chart-hint="{{ $chartKey }}">{{ $p30['charts'][$chartKey]['hint'] }}</p>
                                </div>
                            </div>
                            <span
                                class="shrink-0 rounded-lg border border-zinc-700/90 bg-zinc-950/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-400"
                                data-range-label
                            >{{ $p30['range_label'] }}</span>
                        </div>

                        <div
                            class="mt-4 flex min-h-0 flex-1 flex-col rounded-xl border border-zinc-800/80 bg-zinc-950/45 p-4 ring-1 ring-inset ring-white/[0.04] sm:p-5"
                        >
                        <div
                            class="relative flex min-h-[10.5rem] w-full min-w-0 flex-1 flex-row flex-nowrap items-stretch justify-between gap-0.5 overflow-x-auto overscroll-x-contain rounded-lg bg-[linear-gradient(to_top,rgba(39,39,42,0.45)_1px,transparent_1px)] bg-[length:100%_2.25rem] bg-bottom pb-1 sm:gap-1 [scrollbar-width:thin]"
                            role="img"
                                aria-label="{{ $p30['charts'][$chartKey]['title'] }}"
                                data-chart-bars="{{ $chartKey }}"
                            ></div>
                            <p class="mt-3 border-t border-zinc-800/70 pt-3 text-center text-[10px] leading-relaxed text-zinc-500">
                                Échelle relative au maximum sur la période · survol d’une barre pour le détail
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="grid gap-4 lg:grid-cols-5 lg:gap-6">
            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/60 ring-1 ring-white/5 lg:col-span-2">
                <div class="border-b border-zinc-800 px-5 py-4">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Derniers inscrits</h2>
                </div>
                <ul class="divide-y divide-zinc-800/80 p-2">
                    @forelse ($dashboard['recent_users'] as $ru)
                        <li class="flex gap-3 px-3 py-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500 ring-4 ring-indigo-500/20" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-200">{{ $ru['name'] }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $ru['email'] }}</p>
                                <p class="mt-0.5 text-[10px] text-zinc-600">{{ $ru['created_at'] ?? '—' }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-8 text-center text-sm text-zinc-500">Aucun compte pour l’instant.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/60 ring-1 ring-white/5 lg:col-span-3">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-800 px-5 py-4">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Factures récentes</h2>
                    <a href="{{ route('admin.invoices.index') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Tout voir →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[28rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                                <th class="px-5 py-3">N°</th>
                                <th class="px-5 py-3">Objet</th>
                                <th class="px-5 py-3">Statut</th>
                                <th class="px-5 py-3">TTC</th>
                                <th class="px-5 py-3">Payée le</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/80 text-zinc-400">
                            @forelse ($dashboard['recent_invoices'] as $inv)
                                <tr class="transition hover:bg-zinc-800/30">
                                    <td class="px-5 py-3.5 font-mono text-xs font-medium text-zinc-200">{{ $inv['number'] }}</td>
                                    <td class="max-w-[12rem] truncate px-5 py-3.5 text-zinc-300">{{ $inv['label'] ?? '—' }}</td>
                                    <td class="px-5 py-3.5 text-xs">{{ $inv['status'] }}</td>
                                    <td class="px-5 py-3.5 font-medium text-zinc-200">{{ number_format($inv['amount_ttc'], 2, ',', ' ') }} €</td>
                                    <td class="px-5 py-3.5 text-xs">{{ $inv['paid_at'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-zinc-500">Aucune facture.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script id="admin-dashboard-json" type="application/json">{!! json_encode($dashboard['periods'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script>
(function () {
    const el = document.getElementById('admin-dashboard-json');
    if (!el) return;
    const PERIODS = JSON.parse(el.textContent || '{}');

    const chartKeys = ['visitors', 'signups', 'revenue', 'quiz'];
    const gradients = {
        visitors: 'from-indigo-600 to-indigo-400',
        signups: 'from-emerald-600 to-emerald-400',
        revenue: 'from-amber-600 to-amber-400',
        quiz: 'from-violet-600 to-violet-400',
    };

    function formatEuro(n) {
        return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(n);
    }

    function renderBars(key, labels, values, mode) {
        const host = document.querySelector('[data-chart-bars="' + key + '"]');
        if (!host) return;
        const max = Math.max(1, ...values.map(v => Number(v) || 0));
        host.innerHTML = labels.map((l, i) => {
            const v = Number(values[i]) || 0;
            const pct = Math.max(4, Math.round((v / max) * 100));
            let title = l + ' : ' + v;
            if (mode === 'revenue') title = l + ' : ' + formatEuro(v);
            return (
                '<div class="flex h-full min-h-0 min-w-0 flex-1 flex-col gap-1">' +
                '<div class="flex min-h-0 flex-1 flex-col justify-end">' +
                '<div class="w-full rounded-t-md bg-gradient-to-t ' + gradients[key] + ' opacity-90 transition-all duration-300 hover:opacity-100" ' +
                'style="min-height:4px;height:' + pct + '%;" title="' + title.replace(/"/g, '&quot;') + '"></div>' +
                '</div>' +
                '<span class="block min-w-0 shrink break-words text-center text-[9px] font-medium uppercase leading-tight text-zinc-600 sm:text-[10px]">' + l + '</span>' +
                '</div>'
            );
        }).join('');
    }

    function applyKpis(kpis) {
        const fmtMoney = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v) + ' €';
        const rev = document.querySelector('[data-kpi="revenue_ttc"]');
        if (rev) rev.textContent = fmtMoney(Number(kpis.revenue_ttc) || 0);
        const rd = document.querySelector('[data-kpi="revenue_delta"]');
        if (rd) {
            const v = kpis.revenue_delta_pct;
            rd.textContent = v == null ? '— vs période précédente' : ((Number(v) >= 0 ? '+' : '') + v + ' % vs période précédente');
        }
        [['active_courses'], ['completed_courses'], ['users_total'], ['portal_users'], ['new_users_period']].forEach(([k]) => {
            const n = document.querySelector('[data-kpi="' + k + '"]');
            if (n) n.textContent = String(kpis[k] ?? '0');
        });
    }

    function applyPeriod(code) {
        const pack = PERIODS[code];
        if (!pack) return;
        document.querySelectorAll('[data-range-label]').forEach((n) => { n.textContent = pack.range_label; });
        chartKeys.forEach((k) => {
            const c = pack.charts[k];
            const title = document.querySelector('[data-chart-title="' + k + '"]');
            const hint = document.querySelector('[data-chart-hint="' + k + '"]');
            if (title) title.textContent = c.title;
            if (hint) hint.textContent = c.hint;
            const mode = k === 'revenue' ? 'revenue' : 'int';
            renderBars(k, c.labels, c.values, mode);
        });
        applyKpis(pack.kpis);
    }

    let activePeriod = '30j';
    const tabs = document.querySelectorAll('.period-tab');
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activePeriod = tab.dataset.period;
            tabs.forEach((t) => {
                const on = t === tab;
                t.setAttribute('aria-pressed', String(on));
                t.className =
                    'period-tab rounded-lg px-3.5 py-1.5 text-xs font-semibold transition ' +
                    (on ? 'bg-indigo-600 text-white shadow-sm' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800');
            });
            applyPeriod(activePeriod);
        });
    });

    applyPeriod(activePeriod);
})();
</script>
@endpush
