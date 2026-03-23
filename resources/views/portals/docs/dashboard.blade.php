@extends('layouts.admin')

@section('portal_main_max', 'max-w-none w-full')

@section('title', 'Documentation')
@section('topbar_label', 'Sommaire')

@section('content')
    <div class="docs-page w-full min-w-0 px-0 sm:px-2 lg:px-4 xl:px-6 2xl:px-10">
        @include('portals.docs.partials.breadcrumbs', ['node' => null])

        <header class="docs-hero relative mb-12 overflow-hidden rounded-3xl border border-indigo-500/20 bg-gradient-to-br from-indigo-950/80 via-zinc-900/90 to-violet-950/50 p-8 shadow-[0_0_60px_-12px_rgba(99,102,241,0.35)] ring-1 ring-white/10 sm:p-10">
            <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 h-40 w-40 rounded-full bg-violet-600/15 blur-3xl" aria-hidden="true"></div>
            <p class="relative text-[11px] font-semibold uppercase tracking-[0.25em] text-indigo-300/90">Documentation</p>
            <h1 class="relative mt-3 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">Bienvenue dans la doc</h1>
            <p class="relative mt-4 max-w-xl text-base leading-relaxed text-zinc-300">
                Explore les guides et la référence adaptés à ton compte. La navigation à gauche reprend toute l’arborescence des pages auxquelles tu as accès.
            </p>
        </header>

        <section aria-labelledby="docs-root-heading">
            <h2 id="docs-root-heading" class="sr-only">Pages à la racine</h2>
            <ul class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @forelse ($roots as $node)
                    <li>
                        <a
                            href="{{ route('portals.docs.show', ['path' => $node->pathString()]) }}"
                            class="group flex h-full flex-col rounded-2xl border border-zinc-800/90 bg-zinc-900/40 p-5 ring-1 ring-white/5 transition hover:-translate-y-0.5 hover:border-indigo-500/35 hover:bg-zinc-900/70 hover:shadow-lg hover:shadow-indigo-500/10"
                        >
                            <span class="mb-3 flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500/20 to-violet-600/10 text-indigo-300 ring-1 ring-indigo-400/20 transition group-hover:from-indigo-500/30 group-hover:to-violet-500/20">
                                @if ($node->is_folder)
                                    <svg class="size-5 text-amber-400/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M3 7v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z" stroke-linejoin="round"/>
                                    </svg>
                                @else
                                    <svg class="size-5 text-violet-300/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linejoin="round"/>
                                        <path d="M14 2v6h6"/>
                                    </svg>
                                @endif
                            </span>
                            <span class="font-display text-lg font-bold text-white group-hover:text-indigo-100">{{ $node->title }}</span>
                            <span class="mt-2 text-sm text-zinc-500 group-hover:text-zinc-400">
                                {{ $node->is_folder ? 'Dossier' : 'Lire la page' }} →
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="col-span-full rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/30 px-6 py-14 text-center">
                        <p class="text-zinc-500">Aucune page publiée pour l’instant.</p>
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
