@extends('layouts.admin')

@section('title', 'Équipes')
@section('topbar_label', 'Portail collaborateurs')
@section('portal_main_max', 'max-w-none')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Collaboration</p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Équipes</h1>
                <p class="mt-1 max-w-xl text-sm text-zinc-400">
                    @if (auth()->user()?->isAdmin())
                        Vue administrateur : toutes les équipes.
                    @else
                        Les équipes dont vous faites partie et leurs droits.
                    @endif
                </p>
            </div>
            <a href="{{ route('portals.collabs') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Tableau de bord</a>
        </div>

        @include('layouts.partials.flash')

        @if ($teams->isEmpty())
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8 text-center text-sm text-zinc-500 ring-1 ring-white/5">
                Aucune équipe pour l’instant. Un administrateur peut vous affecter à une équipe depuis l’administration.
            </div>
        @else
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                @foreach ($teams as $team)
                    <li>
                        <a href="{{ route('portals.collabs.teams.show', $team) }}"
                           class="block rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700 hover:ring-indigo-500/10">
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="font-display text-lg font-bold text-white">{{ $team->name }}</h2>
                                @if ($team->is_admin_team)
                                    <span class="shrink-0 rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-300">Admin</span>
                                @endif
                            </div>
                            <p class="mt-2 text-xs text-zinc-500">/{{ $team->slug }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
