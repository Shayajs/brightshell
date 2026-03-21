@extends('layouts.admin')
@section('title', 'URSSAF & charges')
@section('topbar_label', 'Déclarations')

@section('content')
@php
    $tauxCotisation = 21.2;
    $tauxFormation  = 0.1;
    $tauxVersement  = 21.3;

    $cotisations = round($caAnnuel * $tauxCotisation / 100, 2);
    $formation   = round($caAnnuel * $tauxFormation / 100, 2);
    $total       = round($caAnnuel * $tauxVersement / 100, 2);
    $net         = round($caAnnuel - $total, 2);

    $plafondME = 77_700;
    $depassement = $caAnnuel > $plafondME;
@endphp

<div class="space-y-8">
    <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-400/90">Auto-entrepreneur</p>
        <h1 class="font-display text-2xl font-bold text-white">URSSAF &amp; charges (aide)</h1>
        <p class="mt-1 max-w-2xl text-sm text-zinc-500">
            Hypothèse <strong class="text-zinc-400">micro-entreprise</strong>, prestations de services BIC, <strong class="text-zinc-400">sans TVA</strong> en sus des prix. Simulation à partir du CA encaissé {{ $year }} (factures marquées payées).
        </p>
    </div>

    @include('admin.declarations._nav')

    @include('layouts.partials.flash')

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">CA encaissé {{ $year }}</p>
            <p class="mt-2 font-display text-2xl font-bold text-white">{{ number_format($caAnnuel, 0, ',', ' ') }} €</p>
            @if ($depassement)
                <p class="mt-1 text-xs font-medium text-red-400">⚠ Plafond ME dépassé ({{ number_format($plafondME, 0, ',', ' ') }} €)</p>
            @else
                <p class="mt-1 text-xs text-zinc-500">Plafond {{ number_format($plafondME, 0, ',', ' ') }} € — {{ number_format(max(0, $plafondME - $caAnnuel), 0, ',', ' ') }} € restants</p>
            @endif
        </article>
        <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Cotisations ({{ $tauxCotisation }}%)</p>
            <p class="mt-2 font-display text-2xl font-bold text-red-400">−{{ number_format($cotisations, 0, ',', ' ') }} €</p>
            <p class="mt-1 text-xs text-zinc-500">Sécu, retraite, prévoyance</p>
        </article>
        <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Formation ({{ $tauxFormation }}%)</p>
            <p class="mt-2 font-display text-2xl font-bold text-orange-400">−{{ number_format($formation, 0, ',', ' ') }} €</p>
            <p class="mt-1 text-xs text-zinc-500">CFP (contrib. formation pro.)</p>
        </article>
        <article class="rounded-2xl border border-emerald-500/30 bg-emerald-500/8 p-5 ring-1 ring-emerald-500/15">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Net estimé</p>
            <p class="mt-2 font-display text-2xl font-bold text-emerald-400">{{ number_format($net, 0, ',', ' ') }} €</p>
            <p class="mt-1 text-xs text-zinc-500">Avant impôts (IR)</p>
        </article>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <h2 class="border-b border-zinc-800 px-5 py-4 font-display text-sm font-bold uppercase tracking-wide text-white">Échéances 2026</h2>
            <div class="divide-y divide-zinc-800/60">
                @foreach ([
                    ['Déclaration CA T4 2025',  '31 jan.',  'Déclaration + paiement trimestriel'],
                    ['Déclaration CA jan.–fév.', '31 mars',  'Si option mensuelle'],
                    ['Déclaration CA T1 2026',  '30 avril', 'Déclaration trimestrielle'],
                    ['Déclaration CA mars–avr.', '31 mai',   'Si option mensuelle'],
                    ['Déclaration CA T2 2026',  '31 juil.', 'Déclaration trimestrielle'],
                    ['Déclaration CA juil.–août','30 sept.', 'Si option mensuelle'],
                    ['Déclaration CA T3 2026',  '31 oct.', 'Déclaration trimestrielle'],
                    ['Déclaration CA sept.–oct.','30 nov.', 'Si option mensuelle'],
                ] as [$label, $date, $note])
                <div class="flex items-start gap-4 px-5 py-3.5">
                    <span class="mt-0.5 shrink-0 rounded-md border border-indigo-500/30 bg-indigo-500/10 px-2 py-0.5 text-[11px] font-bold text-indigo-300">{{ $date }}</span>
                    <div>
                        <p class="text-sm font-medium text-zinc-100">{{ $label }}</p>
                        <p class="text-xs text-zinc-500">{{ $note }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <div class="space-y-4">
            <section class="space-y-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Rappels clés</h2>
                <ul class="space-y-2.5 text-sm text-zinc-400">
                    <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400" aria-hidden="true"></span>Déclarer le CA même à 0 € pour chaque période.</li>
                    <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400" aria-hidden="true"></span>Cotisations calculées sur CA encaissé (pas facturé).</li>
                    <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400" aria-hidden="true"></span>Conserver les factures 10 ans (obligation comptable).</li>
                    <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400" aria-hidden="true"></span>Seuil TVA franchise : 36 800 € (services) — surveiller le dépassement (passage éventuel en EI + TVA).</li>
                    <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400" aria-hidden="true"></span>Plafond ME prestation BIC : 77 700 € sur 2 ans glissants (2026).</li>
                </ul>
            </section>

            <section class="space-y-2 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Liens officiels</h2>
                @foreach ([
                    ['autoentrepreneur.urssaf.fr', 'https://autoentrepreneur.urssaf.fr'],
                    ['impots.gouv.fr', 'https://impots.gouv.fr'],
                    ['mon.urssaf.fr', 'https://mon.urssaf.fr'],
                    ['lautoentrepreneur.fr', 'https://www.lautoentrepreneur.fr'],
                ] as [$label, $href])
                    <a href="{{ $href }}" target="_blank" rel="noopener"
                        class="flex items-center gap-2 rounded-lg border border-zinc-700 px-3 py-2 text-sm text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300">
                        <svg class="h-4 w-4 shrink-0 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        {{ $label }}
                    </a>
                @endforeach
            </section>
        </div>
    </div>

    <p class="text-xs italic text-zinc-600">Simulation indicative — taux 2026 prestation services BIC. Non contractuel. Vérifier sur autoentrepreneur.urssaf.fr.</p>
</div>
@endsection
