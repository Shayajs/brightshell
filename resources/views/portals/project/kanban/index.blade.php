@extends('layouts.admin')

@section('title', 'Kanban — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Kanban — {{ $board->name }}</h1>
        <p class="mt-1 text-sm text-zinc-500">Colonnes et cartes (édition réservée au droit <strong class="text-zinc-400">modifier</strong>).</p>
    </header>

    @can('update', $project)
        <form method="POST" action="{{ route('portals.project.kanban.columns.store', $project) }}" class="flex flex-wrap items-end gap-2">
            @csrf
            <div>
                <label class="block text-xs text-zinc-500">Nouvelle colonne</label>
                <input type="text" name="name" required class="mt-1 rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            </div>
            <button type="submit" class="rounded-lg bg-zinc-700 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-600">Ajouter</button>
        </form>
    @endcan

    <div class="flex gap-4 overflow-x-auto pb-2">
        @foreach ($board->columns as $column)
            <div class="flex w-[min(100%,18rem)] shrink-0 flex-col rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
                <div class="flex items-center justify-between border-b border-zinc-800 px-3 py-2">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-zinc-400">{{ $column->name }}</h2>
                    @can('update', $project)
                        <form method="POST" action="{{ route('portals.project.kanban.columns.destroy', [$project, $column]) }}" onsubmit="return confirm('Supprimer la colonne et ses cartes ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[10px] text-red-400 hover:text-red-300">×</button>
                        </form>
                    @endcan
                </div>
                <div class="flex flex-1 flex-col gap-2 p-2">
                    @foreach ($column->cards as $card)
                        <div class="rounded-lg border border-zinc-700/80 bg-zinc-950/80 p-3">
                            <p class="font-medium text-sm text-zinc-100">{{ $card->title }}</p>
                            @if ($card->body)
                                <p class="mt-1 text-xs text-zinc-500 whitespace-pre-wrap">{{ $card->body }}</p>
                            @endif
                            @can('update', $project)
                                <div class="mt-2 flex flex-wrap gap-2 border-t border-zinc-800 pt-2">
                                    <form method="POST" action="{{ route('portals.project.kanban.cards.move', [$project, $card]) }}" class="flex flex-1 flex-wrap items-center gap-1">
                                        @csrf
                                        <select name="column_id" class="max-w-full rounded border border-zinc-700 bg-zinc-900 px-1 py-0.5 text-[10px] text-white">
                                            @foreach ($board->columns as $c)
                                                <option value="{{ $c->id }}" @selected($c->id === $column->id)>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="text-[10px] font-semibold text-cyan-400">Déplacer</button>
                                    </form>
                                    <form method="POST" action="{{ route('portals.project.kanban.cards.destroy', [$project, $card]) }}" onsubmit="return confirm('Supprimer la carte ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[10px] text-red-400">Suppr.</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                    @endforeach
                    @can('update', $project)
                        <details class="rounded-lg border border-dashed border-zinc-700 p-2">
                            <summary class="cursor-pointer text-[11px] font-semibold text-zinc-500">+ Carte</summary>
                            <form method="POST" action="{{ route('portals.project.kanban.cards.store', [$project, $column]) }}" class="mt-2 space-y-2">
                                @csrf
                                <input type="text" name="title" required placeholder="Titre" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                <textarea name="body" rows="2" placeholder="Détail" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white"></textarea>
                                <button type="submit" class="text-xs font-semibold text-cyan-400">Créer</button>
                            </form>
                        </details>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
