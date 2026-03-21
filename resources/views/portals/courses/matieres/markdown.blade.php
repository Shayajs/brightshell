@extends('layouts.admin')

@section('title', $file->original_name)
@section('topbar_label', $isAdminPreview ? 'Aperçu Markdown' : 'Matières')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
@endpush

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-wrap items-center gap-3 text-sm">
            @if ($isAdminPreview)
                <a href="{{ url()->previous() }}" class="text-zinc-500 hover:text-violet-400">← Retour</a>
            @else
                <a href="{{ route('portals.courses.matieres.show', $subject) }}" class="text-zinc-500 hover:text-violet-400">← {{ $subject->title }}</a>
            @endif
            <span class="text-zinc-700">|</span>
            <a href="{{ route('portals.courses.matieres.download', $file->id) }}"
               class="text-violet-400 hover:text-violet-300">Télécharger le .md</a>
        </div>

        <header class="border-b border-zinc-800 pb-4">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-violet-400/90">Document</p>
            <h1 class="mt-1 font-display text-2xl font-bold text-white">{{ $file->original_name }}</h1>
        </header>

        <article id="md-root" class="student-md-content rounded-2xl border border-zinc-800 bg-zinc-900/40 p-6 ring-1 ring-white/5">
            {!! $html !!}
        </article>
    </div>
@endsection

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>
    <script>
        window.addEventListener('load', function () {
            if (typeof renderMathInElement === 'undefined') return;
            const el = document.getElementById('md-root');
            if (!el) return;
            renderMathInElement(el, {
                delimiters: [
                    { left: '$$', right: '$$', display: true },
                    { left: '$', right: '$', display: false },
                    { left: '\\(', right: '\\)', display: false },
                    { left: '\\[', right: '\\]', display: true },
                ],
                throwOnError: false,
                strict: false,
            });
        });
    </script>
@endpush
