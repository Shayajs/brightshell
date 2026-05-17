@php
    $current = $state['current'] ?? 0;
    $total = $state['total'] ?? 0;
    $step = $state['step'] ?? null;
    $status = $state['status'] ?? null;
    $pct = $total > 0 ? min(100, (int) round(($current / $total) * 100)) : 0;
    $result = $state['result'] ?? null;
@endphp

<div class="space-y-6"
     @if ($status === 'running') wire:poll.1s @endif>
    <form wire:submit.prevent="start" class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Code postal</label>
                <input type="text" wire:model="codePostal" maxlength="5" placeholder="33230"
                       class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Département</label>
                <input type="text" wire:model="departement" maxlength="3" placeholder="33"
                       class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Code NAF</label>
                <input type="text" wire:model="codeNaf" placeholder="46.69B"
                       class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Pages max (×25 résultats)</label>
                <input type="number" wire:model="maxPages" min="1" max="40"
                       class="mt-1 w-full rounded-lg border-zinc-700 bg-zinc-900/70 text-sm text-zinc-100 focus:border-indigo-500/60 focus:ring-0">
            </div>
            <div class="md:col-span-2 flex items-end gap-4 text-xs text-zinc-300">
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" wire:model="withBodacc" class="rounded border-zinc-700 bg-zinc-900 text-indigo-500 focus:ring-indigo-500/40">
                    Enrichissement BODACC
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" wire:model="withGeocoding" class="rounded border-zinc-700 bg-zinc-900 text-indigo-500 focus:ring-indigo-500/40">
                    Géocodage (BAN)
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" wire:model="withWebsiteProbe" class="rounded border-zinc-700 bg-zinc-900 text-indigo-500 focus:ring-indigo-500/40">
                    Sonde site web (HTTPS / âge / plateforme)
                </label>
                @if ($inpiEnabled)
                    <label class="inline-flex cursor-pointer items-center gap-2">
                        <input type="checkbox" wire:model="withInpi" class="rounded border-zinc-700 bg-zinc-900 text-orange-400 focus:ring-orange-500/40">
                        INPI (bilans Hot/Priority)
                    </label>
                @else
                    <span class="text-zinc-500 italic">INPI désactivé (INPI_TOKEN absent)</span>
                @endif
            </div>
        </div>
        @error('codePostal') <p class="mt-2 text-xs text-red-400">{{ $message }}</p> @enderror
        @error('departement') <p class="mt-2 text-xs text-red-400">{{ $message }}</p> @enderror
        @error('codeNaf') <p class="mt-2 text-xs text-red-400">{{ $message }}</p> @enderror

        <div class="mt-5 flex items-center justify-between">
            <p class="text-xs text-zinc-500">
                L'import s'exécute en arrière-plan via la queue.
                Vérifie que <code class="rounded bg-zinc-950 px-1 py-0.5">php artisan queue:work</code> tourne.
            </p>
            <button type="submit"
                    @if ($status === 'running') disabled @endif
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                {{ $status === 'running' ? 'En cours…' : 'Lancer l\'import' }}
            </button>
        </div>
    </form>

    @if ($state)
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">État de l'import</p>

            @if ($status === 'running')
                <div class="mt-3 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-300">{{ ucfirst($step ?? 'init') }}</span>
                        <span class="tabular-nums text-zinc-400">{{ $current }} / {{ $total ?: '?' }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded bg-zinc-800">
                        <div class="h-full rounded bg-indigo-500 transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @elseif ($status === 'done' && $result)
                <div class="mt-3 grid gap-3 sm:grid-cols-4">
                    <div class="rounded-lg border border-slate-700 bg-slate-900/50 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-zinc-500">Récupérés</p>
                        <p class="text-xl font-bold text-zinc-100 tabular-nums">{{ $result['fetched'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-emerald-400">Conservés</p>
                        <p class="text-xl font-bold text-emerald-300 tabular-nums">{{ $result['kept'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg border border-amber-500/30 bg-amber-500/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-amber-400">Exclus</p>
                        <p class="text-xl font-bold text-amber-300 tabular-nums">{{ $result['excluded'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-700 bg-slate-900/50 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-zinc-500">Durée</p>
                        <p class="text-xl font-bold text-zinc-100 tabular-nums">{{ number_format(($result['duration_ms'] ?? 0) / 1000, 1) }} s</p>
                    </div>
                </div>

                @if (! empty($result['by_band']))
                    <div class="mt-4">
                        <p class="text-[10px] uppercase tracking-wider text-zinc-500">Répartition par bande</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($result['by_band'] as $band => $count)
                                <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-700 bg-slate-900/50 px-2 py-1 text-xs">
                                    <span class="font-semibold text-zinc-300">{{ $band }}</span>
                                    <span class="tabular-nums text-zinc-100">{{ $count }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! empty($result['csv_path']))
                    <p class="mt-4 text-xs text-zinc-400">
                        CSV exporté : <code class="break-all rounded bg-zinc-950 px-1.5 py-0.5 text-zinc-300">{{ $result['csv_path'] }}</code>
                    </p>
                @endif

                <a href="{{ route('prospects.index') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Voir les prospects importés
                </a>
            @elseif ($status === 'failed')
                <div class="mt-3 rounded-lg border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-300">
                    Échec : {{ $state['error'] ?? 'erreur inconnue' }}
                </div>
            @endif
        </div>
    @endif
</div>
