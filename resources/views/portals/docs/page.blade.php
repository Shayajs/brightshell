@extends('layouts.admin')

@section('title', $node->title.' — Documentation')
@section('topbar_label', $node->title)

@section('content')
    <div class="docs-page mx-auto max-w-3xl px-1 sm:px-0 lg:max-w-3xl xl:max-w-[46rem]">
        @include('portals.docs.partials.breadcrumbs', ['node' => $node])

        <article class="docs-article rounded-3xl border border-indigo-500/15 bg-gradient-to-b from-zinc-900/50 to-zinc-950/80 p-6 shadow-[0_0_80px_-24px_rgba(99,102,241,0.25)] ring-1 ring-white/10 sm:p-8 lg:p-10">
            <header class="mb-8 border-b border-zinc-800/80 pb-8">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-violet-400/90">Article</p>
                <h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-white sm:text-[2.15rem] sm:leading-tight">{{ $node->title }}</h1>
            </header>

            {{-- Si le Markdown commence par un # titre, on évite le doublon avec le h1 ci-dessus --}}
            <div class="docs-prose docs-prose--skip-first-md-h1">
                @if ($html !== '')
                    {!! $html !!}
                @else
                    <p class="text-zinc-500">Cette page n’a pas encore de contenu.</p>
                @endif
            </div>
        </article>
    </div>
@endsection
