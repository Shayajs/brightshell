<div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 space-y-4" data-remove-item>
    <div class="flex items-center justify-between gap-3">
        <span class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">
            {{ !empty($exp['entreprise']) ? $exp['entreprise'] : 'Nouvelle expérience' }}
        </span>
        <button type="button" data-remove-item
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400"
            aria-label="Supprimer">
            <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Entreprise *</label>
            <input type="text" name="items[{{ $i }}][entreprise]" value="{{ $exp['entreprise'] ?? '' }}"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                placeholder="BrightShell">
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Poste</label>
            <input type="text" name="items[{{ $i }}][poste]" value="{{ $exp['poste'] ?? '' }}"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                placeholder="Développeur Full Stack">
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Lieu</label>
            <input type="text" name="items[{{ $i }}][lieu]" value="{{ $exp['lieu'] ?? '' }}"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                placeholder="Paris">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Début</label>
                <input type="text" name="items[{{ $i }}][date_debut]" value="{{ $exp['date_debut'] ?? '' }}"
                    class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                    placeholder="Jan. 2024">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Fin</label>
                <input type="text" name="items[{{ $i }}][date_fin]" value="{{ $exp['date_fin'] ?? '' }}"
                    class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                    placeholder="Présent">
            </div>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Description</label>
            <textarea name="items[{{ $i }}][description]" rows="2"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                placeholder="Mission principale…">{{ $exp['description'] ?? '' }}</textarea>
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Réalisations <span class="normal-case font-normal text-zinc-600">(une par ligne)</span></label>
            <textarea name="items[{{ $i }}][realisations_raw]" rows="3"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                placeholder="Développement de l'API…">{{ implode("\n", $exp['realisations'] ?? []) }}</textarea>
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Technologies <span class="normal-case font-normal text-zinc-600">(séparées par virgule)</span></label>
            <textarea name="items[{{ $i }}][technologies_raw]" rows="3"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                placeholder="PHP, Laravel, Docker…">{{ implode(', ', $exp['technologies'] ?? []) }}</textarea>
        </div>
    </div>
</div>
