@extends('layouts.admin')
@section('title', 'CV — Compétences')
@section('topbar_label', 'CV — Compétences')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.cv.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Mon CV</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">Compétences</span>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.cv.competences.update') }}" class="space-y-6">
        @csrf

        @php
        $cats = [
            'langages_preferes'  => ['label' => 'Langages préférés',  'has_level' => true],
            'langages_maitrises' => ['label' => 'Langages maîtrisés', 'has_level' => true],
            'langages_connus'    => ['label' => 'Langages connus',    'has_level' => true],
            'frameworks'         => ['label' => 'Frameworks',         'has_level' => true],
            'outils_devops'      => ['label' => 'Outils & DevOps',    'has_level' => true],
            'bases_de_donnees'   => ['label' => 'Bases de données',   'has_level' => true],
        ];
        @endphp

        @foreach ($cats as $key => $cat)
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">{{ $cat['label'] }}</h2>
                <button type="button" data-add-skill data-section="{{ $key }}"
                    class="flex items-center gap-1.5 rounded-lg border border-dashed border-zinc-700 px-3 py-1.5 text-xs font-medium text-zinc-400 transition hover:border-indigo-500/60 hover:text-indigo-300">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter
                </button>
            </div>
            <div class="space-y-2" data-skill-list="{{ $key }}">
                @foreach (($competences[$key] ?? []) as $j => $skill)
                <div class="skill-row flex items-center gap-2">
                    <input type="text" name="{{ $key }}[{{ $j }}][nom]" value="{{ $skill['nom'] ?? '' }}" placeholder="Nom"
                        class="flex-1 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    <input type="number" name="{{ $key }}[{{ $j }}][niveau]" value="{{ $skill['niveau'] ?? '' }}" placeholder="%" min="0" max="100"
                        class="w-20 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    <input type="text" name="{{ $key }}[{{ $j }}][emoji]" value="{{ $skill['emoji'] ?? '' }}" placeholder="emoji"
                        class="w-16 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    <input type="color" name="{{ $key }}[{{ $j }}][color]" value="{{ $skill['color'] ?? '#6366f1' }}" title="Couleur"
                        class="h-9 w-12 shrink-0 cursor-pointer rounded-lg border border-zinc-700 bg-zinc-950/80 p-1">
                    <button type="button" data-remove-skill
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400"
                        aria-label="Supprimer">
                        <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Langues --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Langues</h2>
                <button type="button" data-add-langue
                    class="flex items-center gap-1.5 rounded-lg border border-dashed border-zinc-700 px-3 py-1.5 text-xs font-medium text-zinc-400 transition hover:border-indigo-500/60 hover:text-indigo-300">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter
                </button>
            </div>
            <div class="space-y-2" id="langue-list">
                @foreach (($competences['langues'] ?? []) as $j => $lang)
                <div class="langue-row flex items-center gap-2">
                    <input type="text" name="langues[{{ $j }}][nom]" value="{{ $lang['nom'] ?? '' }}" placeholder="Langue"
                        class="flex-1 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    <input type="text" name="langues[{{ $j }}][niveau]" value="{{ $lang['niveau'] ?? '' }}" placeholder="Natif, B2…"
                        class="w-36 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    <button type="button" data-remove-langue
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400"
                        aria-label="Supprimer">
                        <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                Enregistrer tout
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const counters = {};

    @foreach ($cats as $key => $cat)
    counters['{{ $key }}'] = {{ count($competences[$key] ?? []) }};
    @endforeach

    let langueIdx = {{ count($competences['langues'] ?? []) }};

    // Skills par section
    document.querySelectorAll('[data-add-skill]').forEach(btn => {
        btn.addEventListener('click', () => {
            const sec  = btn.dataset.section;
            const idx  = counters[sec]++;
            const list = document.querySelector(`[data-skill-list="${sec}"]`);
            const row  = document.createElement('div');
            row.className = 'skill-row flex items-center gap-2';
            row.innerHTML = `
                <input type="text" name="${sec}[${idx}][nom]" placeholder="Nom" class="flex-1 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                <input type="number" name="${sec}[${idx}][niveau]" placeholder="%" min="0" max="100" class="w-20 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                <input type="text" name="${sec}[${idx}][emoji]" placeholder="emoji" class="w-16 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                <input type="color" name="${sec}[${idx}][color]" value="#6366f1" title="Couleur" class="h-9 w-12 shrink-0 cursor-pointer rounded-lg border border-zinc-700 bg-zinc-950/80 p-1">
                <button type="button" data-remove-skill class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400" aria-label="Supprimer">
                    <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>`;
            list.appendChild(row);
            row.querySelector('[data-remove-skill]').addEventListener('click', () => row.remove());
        });
    });

    document.querySelectorAll('[data-remove-skill]').forEach(btn => btn.addEventListener('click', () => btn.closest('.skill-row').remove()));

    // Langues
    document.querySelector('[data-add-langue]').addEventListener('click', () => {
        const list = document.getElementById('langue-list');
        const row  = document.createElement('div');
        row.className = 'langue-row flex items-center gap-2';
        row.innerHTML = `
            <input type="text" name="langues[${langueIdx}][nom]" placeholder="Langue" class="flex-1 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            <input type="text" name="langues[${langueIdx}][niveau]" placeholder="Natif, B2…" class="w-36 rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            <button type="button" data-remove-langue class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400" aria-label="Supprimer">
                <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>`;
        list.appendChild(row);
        row.querySelector('[data-remove-langue]').addEventListener('click', () => row.remove());
        langueIdx++;
    });

    document.querySelectorAll('[data-remove-langue]').forEach(btn => btn.addEventListener('click', () => btn.closest('.langue-row').remove()));
});
</script>
@endsection
