<div class="space-y-4">
    {{-- ─── Barre de filtres ─────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-4">
        <div class="grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Rechercher</label>
                <div class="mt-1 flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900/70 px-3 py-1.5 focus-within:border-indigo-500/60">
                    <svg class="h-4 w-4 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Nom, SIREN, dirigeant, NAF, ville…"
                        class="flex-1 border-0 bg-transparent p-0 text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:ring-0">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Niveau</label>
                <select wire:model.live="band"
                        class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
                    <option value="">Toutes bandes</option>
                    <option value="hot">Hot (≥120)</option>
                    <option value="priority">Prioritaire</option>
                    <option value="standard">Standard</option>
                    <option value="watch">Veille</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Effectif</label>
                <select wire:model.live="effectif"
                        class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
                    @foreach ($effectifOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Code postal</label>
                <input type="text"
                       wire:model.live.debounce.300ms="codePostal"
                       maxlength="5"
                       placeholder="33230"
                       class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Département</label>
                <input type="text"
                       wire:model.live.debounce.300ms="departement"
                       maxlength="3"
                       placeholder="33"
                       class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Besoin détecté</label>
                <select wire:model.live="besoin"
                        class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
                    @foreach ($besoinsOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">État du site</label>
                <select wire:model.live="webEtat"
                        class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
                    <option value="">Tous</option>
                    <option value="absent">Pas de site</option>
                    <option value="mort">Site mort (4xx/5xx)</option>
                    <option value="vieux">Site vieux (≥ 3 ans)</option>
                    <option value="no_https">Sans HTTPS</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-zinc-400">
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input type="checkbox" wire:model.live="sansDigital"
                       class="rounded border-zinc-700 bg-zinc-900 text-indigo-500 focus:ring-indigo-500/40">
                Sans site web
            </label>
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input type="checkbox" wire:model.live="nonTraitesOnly"
                       class="rounded border-zinc-700 bg-zinc-900 text-indigo-500 focus:ring-indigo-500/40">
                Non traités uniquement
            </label>
            @if (config('prospects.home.lat') && config('prospects.home.long'))
                <label class="inline-flex items-center gap-2">
                    Rayon
                    <input type="range" wire:model.live.debounce.500ms="rayonKm" min="0" max="200" step="5"
                           class="accent-indigo-500">
                    <span class="tabular-nums w-16 text-zinc-200">{{ $rayonKm > 0 ? $rayonKm . ' km' : 'off' }}</span>
                </label>
            @endif
            <div class="ml-auto flex items-center gap-3">
                <span class="tabular-nums text-zinc-500">{{ number_format($totalDisplayed) }} résultat(s)</span>
                <button type="button" wire:click="reset_filters"
                        class="rounded-md border border-zinc-700 px-2 py-1 text-zinc-300 hover:border-red-400/40 hover:text-red-300">
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Table ───────────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-800/30">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm">
                <thead class="bg-slate-900/70 text-xs uppercase tracking-wider text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">
                            <button wire:click="sortBy('nom_entreprise')" class="inline-flex items-center gap-1 hover:text-zinc-200">
                                Boîte
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold">Secteur & Ville</th>
                        <th class="px-4 py-3 text-left font-semibold">Dirigeant</th>
                        <th class="px-4 py-3 text-left font-semibold">Effectif</th>
                        <th class="px-4 py-3 text-left font-semibold">
                            <button wire:click="sortBy('score_global')" class="inline-flex items-center gap-1 hover:text-zinc-200">
                                Niveau d'intérêt
                                @if ($sortBy === 'score_global')
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="{{ $sortDir === 'asc' ? '18 15 12 9 6 15' : '6 9 12 15 18 9' }}"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold">Web / Soft</th>
                        <th class="px-4 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/70">
                    @forelse ($prospects as $p)
                        <tr wire:key="prospect-{{ $p->id }}"
                            wire:click="open({{ $p->id }})"
                            class="cursor-pointer transition hover:bg-slate-800/40 {{ $p->traite ? 'opacity-60' : '' }}">
                            {{-- Boîte --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-prospects.company-logo :prospect="$p" />
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-white">{{ $p->nom_entreprise }}</p>
                                        <p class="text-xs tabular-nums text-zinc-500">SIREN {{ $p->siren }}</p>
                                    </div>
                                </div>
                            </td>
                            {{-- Secteur & Ville --}}
                            <td class="px-4 py-3">
                                @if ($p->code_naf)
                                    <span class="inline-flex rounded-md bg-slate-700/60 px-2 py-0.5 font-mono text-xs text-zinc-200">{{ $p->code_naf }}</span>
                                @endif
                                <p class="mt-1 text-xs text-zinc-400">{{ $p->ville ?? '—' }}</p>
                                @if ($p->distance_km_home !== null)
                                    <p class="text-[10px] {{ $p->distance_km_home <= (int) config('prospects.home.radius_km', 30) ? 'text-emerald-400' : 'text-zinc-500' }}">
                                        {{ $p->distance_km_home }} km
                                    </p>
                                @endif
                            </td>
                            {{-- Dirigeant --}}
                            <td class="px-4 py-3">
                                <p class="text-zinc-200">{{ trim(($p->prenom_dirigeant ?? '') . ' ' . ($p->nom_dirigeant ?? '')) ?: '—' }}</p>
                                @if ($p->linkedin_search_url)
                                    <a href="{{ $p->linkedin_search_url }}" target="_blank" rel="noopener" wire:click.stop
                                       class="inline-flex items-center gap-1 text-[10px] text-blue-400 hover:text-blue-300">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04s-2.14 1.45-2.14 2.95v5.66H9.36V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.59 0 4.27 2.36 4.27 5.43v6.31zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.55C0 23.23.79 24 1.77 24h20.44C23.2 24 24 23.23 24 22.28V1.72C24 .77 23.2 0 22.22 0z"/></svg>
                                        LinkedIn
                                    </a>
                                @endif
                            </td>
                            {{-- Effectif --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-md bg-zinc-800 px-2 py-0.5 text-xs text-zinc-300">
                                    {{ $effectifOptions[$p->tranche_effectif] ?? ($p->tranche_effectif ?: 'n/d') }}
                                </span>
                            </td>
                            {{-- Niveau --}}
                            <td class="px-4 py-3">
                                <x-prospects.band-badge :prospect="$p" />
                            </td>
                            {{-- Mini barres + needs chips --}}
                            <td class="px-4 py-3">
                                <x-prospects.mini-bars :website="$p->score_website" :software="$p->score_software" />
                                @php
                                    $topNeeds = array_slice($p->needs, 0, 2);
                                @endphp
                                @if ($topNeeds)
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($topNeeds as $n)
                                            @php
                                                $targets = $n['targets'] ?? ['*'];
                                                $isWeb = in_array('website', $targets, true) && ! in_array('software', $targets, true);
                                                $isSoft = in_array('software', $targets, true) && ! in_array('website', $targets, true);
                                                $chipColor = $isWeb ? 'text-cyan-300 border-cyan-500/30 bg-cyan-500/10'
                                                    : ($isSoft ? 'text-purple-300 border-purple-500/30 bg-purple-500/10'
                                                    : 'text-amber-300 border-amber-500/30 bg-amber-500/10');
                                            @endphp
                                            <span class="inline-flex items-center gap-1 rounded-full border {{ $chipColor }} px-1.5 py-0.5 text-[10px]"
                                                  title="{{ $n['why'] ?? '' }}">
                                                +{{ (int) $n['points'] }}
                                                <span class="opacity-80">{{ str_replace('_', ' ', (string) ($n['key'] ?? '')) }}</span>
                                            </span>
                                        @endforeach
                                        @if (count($p->needs) > 2)
                                            <span class="text-[10px] text-zinc-500">+{{ count($p->needs) - 2 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ $p->fiche_gouv_url }}" target="_blank" rel="noopener" wire:click.stop
                                       title="Voir la fiche officielle"
                                       class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-zinc-700 bg-zinc-900 text-zinc-300 hover:border-indigo-500/50 hover:text-indigo-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                                    </a>
                                    @if (! $p->traite)
                                        <button type="button" wire:click.stop="marquerTraite({{ $p->id }})"
                                                title="Marquer comme traité"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:border-emerald-400/60 hover:bg-emerald-500/15">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>
                                    @else
                                        <span class="inline-flex h-7 items-center justify-center rounded-md border border-zinc-700 px-2 text-[10px] uppercase tracking-wider text-zinc-500">traité</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-zinc-500">
                                Aucun prospect ne correspond à ces filtres.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($prospects->hasPages())
            <div class="border-t border-slate-800 bg-slate-900/40 px-4 py-2">
                {{ $prospects->links() }}
            </div>
        @endif
    </div>
</div>
