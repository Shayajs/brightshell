@extends('layouts.admin')

@section('title', $project->name)
@section('topbar_label', $project->name)

@section('content')
    <div class="space-y-8">
        <p class="text-sm text-zinc-500">
            <a href="{{ route('portals.project') }}" class="text-cyan-400/90 hover:text-cyan-300">← Tous les projets</a>
        </p>

        @include('portals.project.partials.subnav', ['project' => $project])

        <header class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $project->name }}</h1>
                @if ($project->isArchived())
                    <span class="rounded-md border border-zinc-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-400">Archivé</span>
                @endif
            </div>
            @if ($project->company)
                <p class="text-sm text-zinc-500">Société : <span class="text-zinc-300">{{ $project->company->name }}</span></p>
            @endif
            @if ($project->description)
                <p class="max-w-3xl text-sm leading-relaxed text-zinc-400">{{ $project->description }}</p>
            @endif
        </header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['label' => 'Rendez-vous', 'hint' => 'Créneaux et lieux.', 'count' => $project->appointments_count, 'route' => 'portals.project.appointments.index'],
                ['label' => 'Notes', 'hint' => 'Annotations et comptes rendus.', 'count' => $project->notes_count, 'route' => 'portals.project.notes.index'],
                ['label' => 'Kanban', 'hint' => 'Cartes par colonne.', 'count' => $kanbanCardsCount, 'route' => 'portals.project.kanban.index'],
                ['label' => 'Demandes', 'hint' => 'Demandes projet, lien support optionnel.', 'count' => $project->requests_count, 'route' => 'portals.project.requests.index'],
                ['label' => 'Prix & devis', 'hint' => 'Lignes HT / TVA (hors factures légales).', 'count' => $project->price_items_count, 'route' => 'portals.project.prices.index'],
                ['label' => 'Documents', 'hint' => 'Fichiers partagés.', 'count' => $project->documents_count, 'route' => 'portals.project.documents.index'],
                ['label' => 'Cahier des charges', 'hint' => 'Sections rédactionnelles.', 'count' => $project->spec_sections_count, 'route' => 'portals.project.specs.index'],
                ['label' => 'Contrats', 'hint' => 'Références et pièces signées.', 'count' => $project->contracts_count, 'route' => 'portals.project.contracts.index'],
            ] as $card)
                <a
                    href="{{ route($card['route'], $project) }}"
                    class="group flex flex-col rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-cyan-500/30 hover:ring-cyan-500/10"
                >
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-display text-sm font-bold text-zinc-200 group-hover:text-cyan-200/90">{{ $card['label'] }}</h2>
                        <span class="shrink-0 rounded-md bg-zinc-800/80 px-2 py-0.5 font-mono text-xs text-zinc-400">{{ $card['count'] }}</span>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-zinc-500">{{ $card['hint'] }}</p>
                    <span class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-cyan-500/70 group-hover:text-cyan-400">Ouvrir →</span>
                </a>
            @endforeach
        </div>
    </div>
@endsection
