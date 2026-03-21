@extends('layouts.admin')

@section('title', 'Collaborateurs')
@section('topbar_label', 'Portail collaborateurs')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Production</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Espace collaborateurs</h1>
            <p class="max-w-2xl text-sm leading-relaxed text-zinc-400">
                Bonjour <strong class="font-medium text-zinc-200">{{ $user->name }}</strong>. Ici seront regroupées les missions, tâches et livrables
                partagés avec l’équipe BrightShell.
            </p>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Missions</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-xs text-zinc-500">À connecter à la base projets</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tâches</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-xs text-zinc-500">Kanban &amp; deadlines à venir</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700 sm:col-span-2 xl:col-span-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Équipe</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">BS</p>
                <p class="mt-1 text-xs text-zinc-500">Permissions par rôle</p>
            </article>
        </div>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <h2 class="border-b border-zinc-800 px-5 py-4 font-display text-sm font-bold uppercase tracking-wide text-white">Prochainement</h2>
            <div class="p-5 text-sm leading-relaxed text-zinc-400">
                <p>
                    Tableaux de bord par projet, pièces jointes, commentaires internes et suivi du temps —
                    le tout aligné sur les accès
                    <span class="ml-1 inline-flex rounded-md border border-indigo-500/30 bg-indigo-500/10 px-2 py-0.5 text-xs font-semibold text-indigo-300">collaborator</span>.
                </p>
            </div>
        </section>
    </div>
@endsection
