@extends('layouts.admin')

@section('title', 'Projets')
@section('topbar_label', 'Portail projets')

@can('create', \App\Models\Project::class)
    @push('topbar_extra')
        <a href="{{ route('portals.project.create') }}"
            class="flex items-center gap-2 rounded-lg border border-cyan-500/40 bg-cyan-600/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-cyan-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouveau projet
        </a>
    @endpush
@endcan

@section('content')
    <div class="space-y-10">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-cyan-400/90">Espace projet</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Vos projets</h1>
            <p class="max-w-2xl text-sm leading-relaxed text-zinc-400">
                Bonjour <strong class="font-medium text-zinc-200">{{ $user->greetingFirstName() ?: $user->name }}</strong>.
                Rendez-vous, notes, kanban, demandes et documents seront regroupés ici par projet.
            </p>
        </header>

        @if ($projects->isEmpty())
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8 text-center ring-1 ring-white/5">
                <p class="text-sm text-zinc-400">Aucun projet ne vous est associé pour le moment.</p>
                @can('create', \App\Models\Project::class)
                    <a href="{{ route('portals.project.create') }}"
                        class="mt-5 inline-flex items-center gap-2 rounded-lg border border-cyan-500/40 bg-cyan-600/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Créer un projet
                    </a>
                @endcan
            </div>
        @else
            <section class="space-y-4">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Liste</h2>
                <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($projects as $proj)
                        <li>
                            <a
                                href="{{ route('portals.project.show', $proj) }}"
                                class="group flex flex-col rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-cyan-500/30 hover:ring-cyan-500/10"
                            >
                                <span class="flex items-start justify-between gap-2">
                                    <span class="font-display text-lg font-bold text-white group-hover:text-cyan-200/90">{{ $proj->name }}</span>
                                    @if ($proj->isArchived())
                                        <span class="shrink-0 rounded-md border border-zinc-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-400">Archivé</span>
                                    @endif
                                </span>
                                @if ($proj->company)
                                    <span class="mt-1 text-xs text-zinc-500">{{ $proj->company->name }}</span>
                                @endif
                                @if ($proj->description)
                                    <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-zinc-500">{{ $proj->description }}</p>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
