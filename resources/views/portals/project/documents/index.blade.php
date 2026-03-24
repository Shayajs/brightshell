@extends('layouts.admin')

@section('title', 'Documents — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Documents</h1>
        <p class="mt-1 text-sm text-zinc-500">Téléchargement selon le droit <strong class="text-zinc-400">télécharger</strong>.</p>
    </header>

    @can('update', $project)
        <form method="POST" action="{{ route('portals.project.documents.store', $project) }}" enctype="multipart/form-data" class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            @csrf
            <h2 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Envoyer un fichier</h2>
            <div class="mt-3 flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-zinc-500">Titre (optionnel)</label>
                    <input type="text" name="title" class="mt-1 rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs text-zinc-500">Fichier (max 25 Mo)</label>
                    <input type="file" name="file" required class="mt-1 block text-sm text-zinc-400">
                </div>
                <button type="submit" class="rounded-lg bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Envoyer</button>
            </div>
        </form>
    @endcan

    <ul class="space-y-2">
        @forelse ($documents as $doc)
            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-800 bg-zinc-900/40 px-4 py-3">
                <div>
                    <p class="font-medium text-zinc-200">{{ $doc->title }}</p>
                    <p class="text-xs text-zinc-500">
                        @if ($doc->isFolder())
                            Dossier
                        @else
                            {{ $doc->mime ?: 'fichier' }}
                            @if ($doc->size_bytes)
                                — {{ number_format($doc->size_bytes / 1024, 1) }} Ko
                            @endif
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if (! $doc->isFolder())
                        @can('download', $project)
                            <a href="{{ route('portals.project.documents.download', [$project, $doc]) }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300">Télécharger</a>
                        @endcan
                    @endif
                    @can('update', $project)
                        <form method="POST" action="{{ route('portals.project.documents.destroy', [$project, $doc]) }}" onsubmit="return confirm('Supprimer ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300">Supprimer</button>
                        </form>
                    @endcan
                </div>
            </li>
        @empty
            <li class="text-center text-sm text-zinc-500">Aucun document.</li>
        @endforelse
    </ul>
</div>
@endsection
