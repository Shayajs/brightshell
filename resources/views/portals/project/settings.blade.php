@extends('layouts.admin')

@section('title', 'Paramètres')
@section('topbar_label', 'Portail projets')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-cyan-400/90">Réglages</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Paramètres du portail projets</h1>
            <p class="max-w-2xl text-sm text-zinc-400">
                Les préférences spécifiques à ce portail (notifications par projet, affichage) seront ajoutées ici.
            </p>
        </header>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <p class="text-sm text-zinc-500">Aucun paramètre configurable pour l’instant.</p>
        </div>
    </div>
@endsection
