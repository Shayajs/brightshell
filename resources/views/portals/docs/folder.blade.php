@extends('layouts.admin')

@section('title', $node->title.' — Documentation')
@section('topbar_label', $node->title)

@section('content')
    <div class="docs-page mx-auto max-w-4xl px-1 sm:px-0">
        @include('portals.docs.partials.breadcrumbs', ['node' => $node])

        <header class="docs-hero relative mb-10 overflow-hidden rounded-3xl border border-amber-500/15 bg-gradient-to-br from-amber-950/40 via-zinc-900/90 to-zinc-950/90 p-8 ring-1 ring-white/10 sm:p-9">
            <div class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-amber-500/10 blur-3xl" aria-hidden="true"></div>
            <div class="relative flex items-start gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-400/25">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M3 7v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-400/80">Dossier</p>
                    <h1 class="mt-1 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $node->title }}</h1>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-400">Choisis une page ci-dessous pour continuer.</p>
                </div>
            </div>
        </header>

        <ul class="grid gap-3 sm:grid-cols-2">
            @forelse ($children as $child)
                <li>
                    <a
                        href="{{ route('portals.docs.show', ['path' => $child->pathString()]) }}"
                        class="flex items-center gap-3 rounded-xl border border-zinc-800/90 bg-zinc-900/35 px-4 py-4 ring-1 ring-white/5 transition hover:border-indigo-500/30 hover:bg-zinc-900/60"
                    >
                        @if ($child->is_folder)
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400/90">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z" stroke-linejoin="round"/></svg>
                            </span>
                        @else
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-300/80">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            </span>
                        @endif
                        <span class="min-w-0 flex-1 font-medium text-zinc-100">{{ $child->title }}</span>
                        <svg class="size-4 shrink-0 text-zinc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </li>
            @empty
                <li class="col-span-full rounded-xl border border-zinc-800 bg-zinc-900/40 px-5 py-10 text-center text-sm text-zinc-500">
                    Ce dossier est vide pour l’instant.
                </li>
            @endforelse
        </ul>
    </div>
@endsection
