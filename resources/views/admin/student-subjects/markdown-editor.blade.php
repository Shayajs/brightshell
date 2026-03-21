@extends('layouts.admin')

@section('title', $isCreate ? 'Nouveau Markdown' : 'Éditer — '.$file->original_name)
@section('topbar_label', $subject->title)
{{-- Pleine largeur ; padding un peu réduit pour gagner de l’horizontal (override des px du layout) --}}
@section('portal_main_max', 'max-w-none flex min-h-0 w-full flex-1 flex-col !px-3 sm:!px-4 lg:!px-5 2xl:!px-8')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
    <style>
        /* Colonne unique : barre d’outils puis 2 colonnes qui prennent TOUTE la largeur restante */
        .md-editor-page {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex: 1 1 auto;
            min-height: 0;
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }
        .md-editor-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.75rem;
            flex: 1 1 auto;
            min-height: 0;
            min-width: 0;
            width: 100%;
        }
        @media (min-width: 1024px) {
            .md-editor-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                gap: 1rem;
            }
        }
        @media (min-width: 1536px) {
            .md-editor-grid { gap: 1.25rem; }
        }
        .md-editor-pane {
            display: flex;
            flex-direction: column;
            min-height: 0;
            min-width: 0;
            width: 100%;
        }
        .md-editor-pane-body {
            flex: 1 1 auto;
            min-height: 0;
            min-width: 0;
            width: 100%;
        }
        #md-editor-input {
            width: 100%;
            min-height: 14rem;
            box-sizing: border-box;
        }
        #md-editor-preview {
            width: 100%;
            min-height: 14rem;
            box-sizing: border-box;
        }
        @media (min-width: 1024px) {
            .md-editor-page {
                min-height: min(calc(100vh - 9rem), 1500px);
                max-height: calc(100vh - 5.25rem);
            }
            #md-editor-input,
            #md-editor-preview {
                min-height: 0;
                height: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="md-editor-page">
        <div class="flex min-w-0 flex-wrap items-center gap-3 text-sm">
            <a href="{{ route('admin.student-subjects.show', [$user, $subject, 'dossier' => $folder->id]) }}"
               class="text-zinc-500 hover:text-violet-400">← {{ $subject->title }}</a>
            <span class="text-zinc-700">|</span>
            <span class="truncate text-zinc-500">{{ $folder->name }}</span>
        </div>

        @include('layouts.partials.flash')

        <form method="POST"
              action="{{ $isCreate ? route('admin.student-subject-files.store-markdown', [$user, $subject]) : route('admin.student-subject-files.markdown.update', [$user, $subject, $file]) }}"
              class="flex min-h-0 min-w-0 flex-1 flex-col gap-3">
            @csrf
            @if (! $isCreate)
                @method('PUT')
            @endif
            <input type="hidden" name="student_subject_folder_id" value="{{ $folder->id }}">

            {{-- Barre pleine largeur : plus de colonne latérale qui mange 20rem --}}
            <div class="flex min-w-0 shrink-0 flex-col gap-3 rounded-xl border border-zinc-800 bg-zinc-900/50 p-3 ring-1 ring-white/5 sm:p-4 lg:flex-row lg:items-end lg:gap-4">
                <div class="min-w-0 flex-1">
                    <label for="markdown_title" class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Nom du fichier</label>
                    <input type="text" id="markdown_title" name="markdown_title" value="{{ $initialTitle }}" required
                           placeholder="Ma-lecon.md"
                           class="mt-1 w-full min-w-0 rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-600 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30 lg:text-base">
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2 lg:pb-0.5">
                    <button type="button" id="md-preview-refresh"
                            class="rounded-lg border border-zinc-600 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800 lg:px-4 lg:text-sm">
                        Actualiser l’aperçu
                    </button>
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-700 px-3 py-2 text-xs text-zinc-400 hover:bg-zinc-800/80 lg:text-sm">
                        <input type="checkbox" id="md-preview-auto" class="rounded border-zinc-600 bg-zinc-800 text-violet-500" checked>
                        Aperçu auto
                    </label>
                    <button type="submit"
                            class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500 lg:px-5 lg:py-2.5">
                        Enregistrer
                    </button>
                </div>
            </div>

            <div class="md-editor-grid min-h-0 min-w-0 flex-1">
                <div class="md-editor-pane rounded-xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
                    <div class="shrink-0 border-b border-zinc-800 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-zinc-500 sm:px-4">Markdown</div>
                    <textarea id="md-editor-input" name="markdown_body" required
                              class="md-editor-pane-body resize-y rounded-b-xl border-0 bg-zinc-950 px-3 py-3 font-mono text-sm leading-relaxed text-zinc-200 placeholder-zinc-600 focus:ring-0 sm:px-4 sm:py-4 lg:resize-none lg:text-base">{{ $initialBody }}</textarea>
                </div>
                <div class="md-editor-pane rounded-xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
                    <div class="shrink-0 border-b border-zinc-800 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-zinc-500 sm:px-4">Aperçu (callouts + LaTeX)</div>
                    <div id="md-editor-preview" class="student-md-content md-editor-pane-body overflow-auto rounded-b-xl bg-zinc-950/80 p-3 text-sm text-zinc-200 sm:p-4 lg:p-5 lg:text-base [&_*]:max-w-none">
                        <p class="text-zinc-500">Chargement de l’aperçu…</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>
    <script>
        (function () {
            const input = document.getElementById('md-editor-input');
            const preview = document.getElementById('md-editor-preview');
            const btn = document.getElementById('md-preview-refresh');
            const auto = document.getElementById('md-preview-auto');
            const url = @json(route('admin.student-subject-files.markdown.preview-json'));
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function renderMath() {
                if (typeof renderMathInElement === 'undefined') return;
                renderMathInElement(preview, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '$', right: '$', display: false },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '\\[', right: '\\]', display: true },
                    ],
                    throwOnError: false,
                    strict: false,
                });
            }

            let timer = null;
            async function runPreview() {
                if (!input || !preview) return;
                preview.innerHTML = '<p class="text-zinc-500">Rendu…</p>';
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ markdown: input.value }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Erreur');
                    preview.innerHTML = data.html || '';
                    renderMath();
                } catch (e) {
                    preview.innerHTML = '<p class="text-red-400 text-sm">Impossible de générer l’aperçu.</p>';
                }
            }

            function schedule() {
                if (!auto || !auto.checked) return;
                clearTimeout(timer);
                timer = setTimeout(runPreview, 450);
            }

            window.addEventListener('load', function () {
                runPreview();
            });
            btn?.addEventListener('click', function () { runPreview(); });
            input?.addEventListener('input', schedule);
            auto?.addEventListener('change', function () {
                if (auto.checked) runPreview();
            });
        })();
    </script>
@endpush
