@extends('layouts.admin')

@section('title', 'Notes — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Notes</h1>
        <p class="mt-1 text-sm text-zinc-500">Nécessite le droit <strong class="text-zinc-400">annoter</strong> ou <strong class="text-zinc-400">modifier</strong>.</p>
    </header>

    @if (auth()->user()->can('annotate', $project) || auth()->user()->can('update', $project))
        <form method="POST" action="{{ route('portals.project.notes.store', $project) }}" class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            @csrf
            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Nouvelle note</label>
            <textarea name="body" rows="4" required class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white" placeholder="Votre texte…">{{ old('body') }}</textarea>
            <button type="submit" class="mt-3 rounded-lg bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Publier</button>
        </form>
    @endif

    <ul class="space-y-4">
        @forelse ($notes as $note)
            <li class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-4 ring-1 ring-white/5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="text-xs text-zinc-500">{{ $note->user->name }} — {{ $note->created_at->translatedFormat('d M Y H:i') }}</p>
                    @if (($note->user_id === auth()->id() && (auth()->user()->can('annotate', $project) || auth()->user()->can('update', $project))) || auth()->user()->can('update', $project))
                        <form method="POST" action="{{ route('portals.project.notes.destroy', [$project, $note]) }}" onsubmit="return confirm('Supprimer cette note ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-300">Supprimer</button>
                        </form>
                    @endif
                </div>
                <div class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-zinc-300">{{ $note->body }}</div>
            </li>
        @empty
            <li class="text-center text-sm text-zinc-500">Aucune note.</li>
        @endforelse
    </ul>
    @if ($notes->hasPages())
        <div>{{ $notes->links() }}</div>
    @endif
</div>
@endsection
