@extends('layouts.admin')

@section('title', 'Messagerie — '.$team->name)
@section('topbar_label', 'Portail collaborateurs')

{{-- Pleine largeur de la colonne principale + colonne flex pour hauteur utile sur grands écrans --}}
@section('portal_main_max', 'max-w-none')
@section('portal_main_class', 'flex min-h-0 flex-col')

@push('vite')
    @vite(['resources/js/collabs-team-messages.js'])
@endpush

@section('content')
    @php
        $lastId = $messages->last()?->id ?? 0;
    @endphp
    <div class="flex min-h-0 flex-1 flex-col gap-4 lg:gap-5 xl:gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between sm:gap-4">
            <div>
                <a href="{{ route('portals.collabs.teams.show', $team) }}" class="text-sm text-zinc-500 hover:text-indigo-400">← {{ $team->name }}</a>
                <h1 class="mt-1 font-display text-2xl font-bold tracking-tight text-white xl:text-3xl">Messagerie — {{ $team->name }}</h1>
                <p class="mt-1 text-xs text-zinc-500 lg:text-sm">Mise à jour automatique lorsque l’onglet est visible.</p>
            </div>
        </div>

        <div
            id="collab-team-chat"
            class="flex min-h-[22rem] flex-1 flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5 lg:min-h-[calc(100dvh-9.5rem)] xl:rounded-3xl"
            data-poll-url="{{ route('portals.collabs.teams.messages.poll', $team) }}"
            data-store-url="{{ route('portals.collabs.teams.messages.store', $team) }}"
            data-after-id="{{ $lastId }}"
        >
            <div
                id="collab-team-messages-list"
                class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain p-4 sm:p-5 lg:p-6 xl:p-8 xl:space-y-4"
                role="log"
                aria-live="polite"
            >
                @foreach ($messages as $m)
                    <article class="collab-msg rounded-xl border border-zinc-800/80 bg-zinc-950/50 px-4 py-3 sm:px-5 sm:py-4 xl:max-w-5xl xl:rounded-2xl" data-id="{{ $m->id }}">
                        <div class="flex flex-wrap items-baseline justify-between gap-2 text-[11px] text-zinc-500 lg:text-xs">
                            <span class="font-semibold text-zinc-300">{{ $m->user?->name ?? 'Compte supprimé' }}</span>
                            <time datetime="{{ $m->created_at?->toIso8601String() }}">{{ $m->created_at?->format('d/m/Y H:i') }}</time>
                        </div>
                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-zinc-200 lg:text-base">{{ $m->body }}</p>
                    </article>
                @endforeach
            </div>

            <form id="collab-team-message-form" class="shrink-0 border-t border-zinc-800 bg-zinc-950/30 p-4 sm:p-5 lg:p-6 xl:p-8">
                @csrf
                <label for="collab-msg-body" class="sr-only">Message</label>
                <textarea id="collab-msg-body" name="body" rows="3" required maxlength="10000" placeholder="Votre message…"
                          class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 lg:min-h-[5.5rem] lg:text-base xl:rounded-xl xl:px-4 xl:py-3"></textarea>
                <div class="mt-3 flex justify-end lg:mt-4">
                    <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 lg:px-6">Envoyer</button>
                </div>
                <p id="collab-team-msg-error" class="mt-2 hidden text-xs text-red-400" role="alert"></p>
            </form>
        </div>
    </div>
@endsection
