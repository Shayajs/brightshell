@extends('layouts.admin')

@section('title', 'Rendez-vous — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Rendez-vous</h1>
        <p class="mt-1 text-sm text-zinc-500">Planification liée à ce projet.</p>
    </header>

    @can('update', $project)
        <form method="POST" action="{{ route('portals.project.appointments.store', $project) }}" class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            @csrf
            <h2 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Nouveau</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-zinc-500">Titre</label>
                    <input type="text" name="title" required value="{{ old('title') }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs text-zinc-500">Début</label>
                    <input type="datetime-local" name="starts_at" required value="{{ old('starts_at') }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs text-zinc-500">Fin (optionnel)</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-zinc-500">Lieu</label>
                    <input type="text" name="location" value="{{ old('location') }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-zinc-500">Notes</label>
                    <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">{{ old('notes') }}</textarea>
                </div>
            </div>
            <button type="submit" class="rounded-lg bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Ajouter</button>
        </form>
    @endcan

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-800 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                <tr>
                    <th class="px-4 py-3">Quand</th>
                    <th class="px-4 py-3">Titre</th>
                    <th class="px-4 py-3">Lieu</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/60">
                @forelse ($appointments as $a)
                    <tr>
                        <td class="px-4 py-3 text-zinc-300">
                            {{ $a->starts_at->translatedFormat('d M Y, H:i') }}
                            @if ($a->ends_at)
                                <span class="block text-xs text-zinc-500">→ {{ $a->ends_at->translatedFormat('H:i') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-200">{{ $a->title }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $a->location ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $project)
                                <details class="inline text-left">
                                    <summary class="cursor-pointer text-xs font-semibold text-cyan-400 hover:text-cyan-300">Modifier</summary>
                                    <form method="POST" action="{{ route('portals.project.appointments.update', [$project, $a]) }}" class="mt-3 space-y-2 rounded-lg border border-zinc-700 bg-zinc-950 p-3">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="title" value="{{ $a->title }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="datetime-local" name="starts_at" value="{{ $a->starts_at->format('Y-m-d\TH:i') }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="datetime-local" name="ends_at" value="{{ $a->ends_at?->format('Y-m-d\TH:i') }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="text" name="location" value="{{ $a->location }}" placeholder="Lieu" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <textarea name="notes" rows="2" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">{{ $a->notes }}</textarea>
                                        <button type="submit" class="text-xs font-semibold text-cyan-400">Enregistrer</button>
                                    </form>
                                    <form method="POST" action="{{ route('portals.project.appointments.destroy', [$project, $a]) }}" class="mt-2" onsubmit="return confirm('Supprimer ce rendez-vous ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-300">Supprimer</button>
                                    </form>
                                </details>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500">Aucun rendez-vous.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($appointments->hasPages())
            <div class="border-t border-zinc-800 px-4 py-3">{{ $appointments->links() }}</div>
        @endif
    </div>
</div>
@endsection
