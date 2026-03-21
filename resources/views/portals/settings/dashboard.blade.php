@extends('layouts.admin')

@section('title', 'Réglages')
@section('topbar_label', 'Réglages du compte')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Compte</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Paramètres</h1>
            <p class="max-w-2xl text-sm leading-relaxed text-zinc-400">
                <strong class="font-medium text-zinc-200">{{ $user->name }}</strong> — centralisez ici votre profil, vos informations de société et la sécurité du compte.
            </p>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Profil</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">1</p>
                <p class="mt-1 text-xs text-zinc-500">Identité &amp; coordonnées</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Sécurité</p>
                <p class="mt-2 font-display text-2xl font-bold text-emerald-400">OK</p>
                <p class="mt-1 text-xs text-zinc-500">Mot de passe &amp; sessions</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700 sm:col-span-2 xl:col-span-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Facturation</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">—</p>
                <p class="mt-1 text-xs text-zinc-500">Société, TVA, adresses</p>
            </article>
        </div>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <h2 class="border-b border-zinc-800 px-5 py-4 font-display text-sm font-bold uppercase tracking-wide text-white">Sections prévues</h2>
            <div class="p-5">
                <ul class="list-disc space-y-2 pl-5 text-sm leading-relaxed text-zinc-400 marker:text-indigo-500/80">
                    <li>Profil utilisateur (nom, avatar, langue)</li>
                    <li>Société &amp; facturation (SIRET, adresse, mentions)</li>
                    <li>Sécurité (changement de mot de passe, déconnexion des sessions)</li>
                </ul>
            </div>
        </section>
    </div>
@endsection
