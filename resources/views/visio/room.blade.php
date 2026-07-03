@extends('layouts.admin')

@section('title', $room->title)
@section('topbar_label', 'Visio live')

@push('vite')
    @vite('resources/js/visio-room.js')
@endpush

@section('content')
<div class="space-y-4 py-4" id="visio-room-app"
    data-room-slug="{{ $room->slug }}"
    data-context-url="{{ route('visio.runtime.context', $room) }}"
    data-token-url="{{ route('visio.runtime.token', $room) }}"
    data-heartbeat-url="{{ route('visio.runtime.heartbeat', $room) }}"
    data-update-context-url="{{ route('visio.runtime.context.update', $room) }}"
>
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">{{ $room->title }}</h1>
            <p class="mt-1 text-xs text-zinc-500">Salle: {{ $room->slug }}</p>
        </div>
        <div id="visio-status" class="rounded-lg border border-zinc-700 bg-zinc-900/80 px-3 py-1.5 text-xs text-zinc-300">Connexion LiveKit en cours…</div>
    </header>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4 ring-1 ring-white/5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-zinc-100">Flux vidéo / partage écran</h2>
                <button id="visio-share-screen" type="button" class="rounded-md border border-fuchsia-500/40 bg-fuchsia-600/15 px-3 py-1.5 text-xs font-semibold text-fuchsia-200 hover:bg-fuchsia-600/30">
                    Partager écran
                </button>
            </div>
            <div id="visio-livekit-stage" class="min-h-72 rounded-xl border border-zinc-800 bg-zinc-950/70 p-3 text-xs text-zinc-500">
                En attente du stream LiveKit…
            </div>
        </article>

        <article class="space-y-4">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4 ring-1 ring-white/5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-zinc-100">Document partagé</h2>
                    @auth
                        <form id="visio-share-doc-form" class="flex items-center gap-2">
                            <input id="visio-doc-id" type="number" min="1" name="student_subject_file_id" placeholder="ID fichier markdown"
                                class="w-40 rounded-md border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-xs text-white">
                            <button type="submit" class="rounded-md border border-cyan-500/40 bg-cyan-600/15 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-600/30">Partager</button>
                        </form>
                    @endauth
                </div>
                <div id="visio-shared-doc" class="prose prose-invert max-w-none rounded-xl border border-zinc-800 bg-zinc-950/70 p-3 text-xs text-zinc-400">
                    Aucun document partagé pour l’instant.
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4 ring-1 ring-white/5">
                <h2 class="mb-3 font-display text-sm font-bold uppercase tracking-wide text-zinc-100">Devis live</h2>
                <div id="visio-prices" class="space-y-2 text-xs text-zinc-300">
                    Chargement…
                </div>
            </div>
        </article>
    </section>
</div>
@endsection
