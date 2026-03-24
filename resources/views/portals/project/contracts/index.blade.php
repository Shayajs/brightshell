@extends('layouts.admin')

@section('title', 'Contrats — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Contrats</h1>
        <p class="mt-1 text-sm text-zinc-500">Pièce signée : choisir un fichier déjà présent dans les documents du projet.</p>
    </header>

    @can('update', $project)
        <form method="POST" action="{{ route('portals.project.contracts.store', $project) }}" class="space-y-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            @csrf
            <h2 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Nouveau contrat</h2>
            <input type="text" name="reference" required placeholder="Référence" value="{{ old('reference') }}" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <input type="text" name="status" placeholder="Statut (ex. signé, brouillon)" value="{{ old('status', 'draft') }}" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-zinc-500">Effet le</label>
                    <input type="date" name="effective_on" value="{{ old('effective_on') }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="text-xs text-zinc-500">Fin le</label>
                    <input type="date" name="ends_on" value="{{ old('ends_on') }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
            </div>
            <div>
                <label class="text-xs text-zinc-500">Document signé (optionnel)</label>
                <select name="signed_document_id" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                    <option value="">—</option>
                    @foreach ($fileDocuments as $d)
                        <option value="{{ $d->id }}" @selected((int) old('signed_document_id') === $d->id)>{{ $d->title }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Ajouter</button>
        </form>
    @endcan

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-800 text-[10px] font-semibold uppercase text-zinc-500">
                <tr>
                    <th class="px-4 py-3">Référence</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Dates</th>
                    <th class="px-4 py-3">Document</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/60">
                @forelse ($contracts as $c)
                    <tr>
                        <td class="px-4 py-3 text-zinc-200">{{ $c->reference }}</td>
                        <td class="px-4 py-3 text-zinc-400">{{ $c->status }}</td>
                        <td class="px-4 py-3 text-xs text-zinc-500">
                            @if ($c->effective_on) {{ $c->effective_on->translatedFormat('d M Y') }} @else — @endif
                            @if ($c->ends_on) <span class="block">→ {{ $c->ends_on->translatedFormat('d M Y') }}</span> @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-400">{{ $c->signedDocument?->title ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $project)
                                <details class="inline text-left">
                                    <summary class="cursor-pointer text-xs text-cyan-400">Modifier</summary>
                                    <form method="POST" action="{{ route('portals.project.contracts.update', [$project, $c]) }}" class="mt-2 space-y-2 rounded border border-zinc-700 bg-zinc-950 p-3">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="reference" value="{{ $c->reference }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="text" name="status" value="{{ $c->status }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="date" name="effective_on" value="{{ $c->effective_on?->format('Y-m-d') }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="date" name="ends_on" value="{{ $c->ends_on?->format('Y-m-d') }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <select name="signed_document_id" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                            <option value="">—</option>
                                            @foreach ($fileDocuments as $d)
                                                <option value="{{ $d->id }}" @selected($c->signed_document_id === $d->id)>{{ $d->title }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="text-xs font-semibold text-cyan-400">OK</button>
                                    </form>
                                    <form method="POST" action="{{ route('portals.project.contracts.destroy', [$project, $c]) }}" class="mt-2" onsubmit="return confirm('Supprimer ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400">Supprimer</button>
                                    </form>
                                </details>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-zinc-500">Aucun contrat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
