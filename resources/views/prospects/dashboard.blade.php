@extends('layouts.admin')

@section('title', 'Tableau de bord — Prospection')
@section('topbar_label', 'Prospection B2B')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Prospection B2B
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-zinc-400">
                    Lead Scoring 2 couches (points bruts + multiplicateurs non linéaires) appliqué aux entreprises
                    issues de l'API Recherche Entreprises, BODACC et BAN. Cliquez sur une ligne pour ouvrir le détail.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('prospects.import') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nouvel import
                </a>
                <a href="{{ route('prospects.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900/70 px-4 py-2 text-sm font-semibold text-zinc-200 hover:border-zinc-600 hover:bg-zinc-800">
                    Voir la liste
                </a>
            </div>
        </div>

        <x-livewire-mount name="prospects.dashboard" />

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('prospects.index') }}?band=hot"
               class="group rounded-2xl border border-red-500/20 bg-red-500/5 p-5 transition hover:border-red-400/40 hover:bg-red-500/10">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-500/15 text-red-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-red-300">Prospects « Hot »</p>
                        <p class="text-xs text-red-200/70">À appeler dans l'heure — score ≥ 120</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('prospects.index') }}?sans_digital=1&band=priority"
               class="group rounded-2xl border border-cyan-500/20 bg-cyan-500/5 p-5 transition hover:border-cyan-400/40 hover:bg-cyan-500/10">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-cyan-300">Sans site web — priorité</p>
                        <p class="text-xs text-cyan-200/70">Opportunités de refonte digitale</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
