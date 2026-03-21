@extends('layouts.admin')
@section('title', 'Déclarations')
@section('topbar_label', 'Déclarations')

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-400/90">Fiscal &amp; entreprise</p>
        <h1 class="font-display text-2xl font-bold text-white sm:text-3xl">Déclarations</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-400">
            Espace pensé pour l’<strong class="text-zinc-300">auto-entreprise</strong> (sans TVA aujourd’hui). Tu pourras adapter le statut et la TVA plus tard (ex. passage en EI).
        </p>
    </div>

    @include('admin.declarations._nav')

    @include('layouts.partials.flash')

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('admin.declarations.business.edit') }}"
           class="group rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 transition hover:border-violet-500/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-400">Fiche entreprise</p>
            <p class="mt-2 font-display text-lg font-bold text-white">Paramètres &amp; infos légales</p>
            <p class="mt-2 text-sm text-zinc-500">Dénomination, contact public, adresse, SIRET, code NAF, options TVA.</p>
            <p class="mt-4 text-xs font-semibold text-violet-300 group-hover:underline">Modifier →</p>
        </a>

        <a href="{{ route('admin.declarations.urssaf') }}"
           class="group rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 transition hover:border-emerald-500/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-400">Charges &amp; URSSAF</p>
            <p class="mt-2 font-display text-lg font-bold text-white">Simulation &amp; échéances</p>
            <p class="mt-2 text-sm text-zinc-500">Aide à la déclaration sur la base du CA encaissé (factures payées).</p>
            <p class="mt-4 text-xs font-semibold text-emerald-300 group-hover:underline">Ouvrir →</p>
        </a>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 md:col-span-2 xl:col-span-1">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-400">API publique (lecture)</p>
            <p class="mt-2 font-display text-lg font-bold text-white">Infos entreprise en direct</p>
            <p class="mt-2 text-sm text-zinc-500">Un simple <code class="rounded bg-zinc-950 px-1 py-0.5 text-xs text-zinc-300">GET</code> sur le sous-domaine <strong class="text-zinc-400">api</strong> : données volontairement limitées (pas de notes internes, adresse complète et SIRET seulement si tu les actives).</p>
            @if ($apiBaseUrl)
                <p class="mt-4 break-all rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 font-mono text-xs text-sky-300">{{ $apiBaseUrl }}</p>
            @else
                <p class="mt-4 text-xs text-amber-400/90">Définis <code class="text-zinc-400">BRIGHTSHELL_ROOT_DOMAIN</code> ou <code class="text-zinc-400">BRIGHTSHELL_API_HOST</code> pour afficher l’URL exacte.</p>
            @endif
        </div>
    </div>

    <section class="rounded-2xl border border-zinc-800 bg-zinc-950/40 p-5 ring-1 ring-white/5">
        <h2 class="font-display text-xs font-bold uppercase tracking-wider text-zinc-500">Résumé rapide</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-zinc-600">Nom affiché</dt>
                <dd class="font-medium text-zinc-200">{{ $profile->displayName() }}</dd>
            </div>
            <div>
                <dt class="text-zinc-600">Statut</dt>
                <dd class="font-medium text-zinc-200">{{ $profile->legalStatusEnum()->label() }}</dd>
            </div>
            <div>
                <dt class="text-zinc-600">TVA</dt>
                <dd class="font-medium text-zinc-200">{{ $profile->vat_registered ? 'Assujetti · '.$profile->vat_number : 'Non assujetti (réglage actuel)' }}</dd>
            </div>
            <div>
                <dt class="text-zinc-600">Ville</dt>
                <dd class="font-medium text-zinc-200">{{ $profile->city ?: '—' }}</dd>
            </div>
        </dl>
    </section>
</div>
@endsection
