@extends('layouts.admin')
@section('title', 'CV — Certifications')
@section('topbar_label', 'CV — Certifications')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.cv.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Mon CV</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">Certifications</span>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.cv.certifications.update') }}">
        @csrf
        <div class="space-y-4" id="certif-list">
            @foreach ($certifications as $i => $c)
            <div class="certif-item rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">{{ $c['titre'] ?? 'Certification' }}</span>
                    <button type="button" data-remove
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400"
                        aria-label="Supprimer">
                        <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Titre *</label>
                        <input type="text" name="items[{{ $i }}][titre]" value="{{ $c['titre'] ?? '' }}"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Rôle <span class="normal-case font-normal text-zinc-600">(optionnel)</span></label>
                        <input type="text" name="items[{{ $i }}][role]" value="{{ $c['role'] ?? '' }}"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                            placeholder="Animateur, Bénévole…">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Années <span class="normal-case font-normal text-zinc-600">(séparées par virgule)</span></label>
                        <input type="text" name="items[{{ $i }}][annees_raw]" value="{{ implode(', ', $c['annees'] ?? []) }}"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                            placeholder="2015, 2025">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Détails <span class="normal-case font-normal text-zinc-600">(une ligne = un item ; une seule ligne = texte simple)</span></label>
                        @php
                            $details = $c['details'] ?? '';
                            $detailsText = is_array($details) ? implode("\n", $details) : $details;
                        @endphp
                        <textarea name="items[{{ $i }}][details_raw]" rows="3"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">{{ $detailsText }}</textarea>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between">
            <button type="button" id="add-certif"
                class="flex items-center gap-2 rounded-lg border border-dashed border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:border-indigo-500/60 hover:text-indigo-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter
            </button>
            <button type="submit"
                class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                Enregistrer tout
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let idx = {{ count($certifications) }};
    const list = document.getElementById('certif-list');

    document.getElementById('add-certif').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'certif-item rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 space-y-4';
        row.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">Nouvelle certification</span>
                <button type="button" data-remove class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400" aria-label="Supprimer">
                    <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Titre *</label><input type="text" name="items[${idx}][titre]" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Rôle</label><input type="text" name="items[${idx}][role]" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25" placeholder="Animateur…"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Années</label><input type="text" name="items[${idx}][annees_raw]" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25" placeholder="2015, 2025"></div>
                <div class="sm:col-span-2"><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Détails</label><textarea name="items[${idx}][details_raw]" rows="3" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></textarea></div>
            </div>`;
        list.appendChild(row);
        row.querySelector('[data-remove]').addEventListener('click', () => row.remove());
        idx++;
    });

    list.querySelectorAll('[data-remove]').forEach(btn => btn.addEventListener('click', () => btn.closest('.certif-item').remove()));
});
</script>
@endsection
