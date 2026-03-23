@extends('layouts.admin')

@section('title', 'Espace client')
@section('topbar_label', 'Espace client')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Votre espace</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Suivi &amp; documents</h1>
            <p class="max-w-none text-sm leading-relaxed text-zinc-400">
                Bienvenue <strong class="font-medium text-zinc-200">{{ $user->greetingFirstName() ?: $user->name }}</strong>. Ce portail deviendra votre point d’entrée pour les projets,
                contrats, factures et échanges avec BrightShell.
            </p>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Projets actifs</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-xs text-zinc-500">Bientôt synchronisés avec votre compte</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Messages</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-xs text-zinc-500">Tickets &amp; notifications</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700 sm:col-span-2 xl:col-span-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documents</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">—</p>
                <p class="mt-1 text-xs text-zinc-500">Devis, contrats, livrables</p>
            </article>
        </div>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <h2 class="border-b border-zinc-800 px-5 py-4 font-display text-sm font-bold uppercase tracking-wide text-white">Tranquillité</h2>
            <div class="p-5 text-sm leading-relaxed text-zinc-400">
                <p>
                    Un espace clair pour suivre l’avancement sans fouiller les e-mails. Accès réservé au rôle
                    <span class="ml-1 inline-flex rounded-md border border-indigo-500/30 bg-indigo-500/10 px-2 py-0.5 text-xs font-semibold text-indigo-300">client</span>.
                </p>
            </div>
        </section>
    </div>
@endsection
