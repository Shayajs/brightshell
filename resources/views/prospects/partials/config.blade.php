<div class="space-y-6">
    {{-- État APIs --}}
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-6">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Sources de données</h2>
        <ul class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 text-sm">
            <li class="flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3 py-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                <span class="text-zinc-200">Recherche Entreprises</span>
                <span class="ml-auto text-[10px] text-zinc-500">data.gouv.fr</span>
            </li>
            <li class="flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3 py-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                <span class="text-zinc-200">BODACC</span>
                <span class="ml-auto text-[10px] text-zinc-500">DILA</span>
            </li>
            <li class="flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3 py-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                <span class="text-zinc-200">Adresse (BAN)</span>
                <span class="ml-auto text-[10px] text-zinc-500">data.gouv.fr</span>
            </li>
            <li class="flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3 py-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                <span class="text-zinc-200">Géo API</span>
                <span class="ml-auto text-[10px] text-zinc-500">data.gouv.fr</span>
            </li>
            <li class="flex items-center gap-2 rounded-lg border {{ $inseeEnabled ? 'border-emerald-500/20 bg-emerald-500/5' : 'border-zinc-700 bg-zinc-900/50' }} px-3 py-2">
                <span class="inline-flex h-2 w-2 rounded-full {{ $inseeEnabled ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span>
                <span class="text-zinc-200">INSEE Sirene V3</span>
                <span class="ml-auto text-[10px] {{ $inseeEnabled ? 'text-emerald-400' : 'text-zinc-500' }}">{{ $inseeEnabled ? 'actif' : 'INSEE_TOKEN absent' }}</span>
            </li>
            <li class="flex items-center gap-2 rounded-lg border {{ $inpiEnabled ? 'border-emerald-500/20 bg-emerald-500/5' : 'border-zinc-700 bg-zinc-900/50' }} px-3 py-2">
                <span class="inline-flex h-2 w-2 rounded-full {{ $inpiEnabled ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span>
                <span class="text-zinc-200">INPI PISTE (RNE)</span>
                <span class="ml-auto text-[10px] {{ $inpiEnabled ? 'text-emerald-400' : 'text-zinc-500' }}">{{ $inpiEnabled ? 'actif' : 'INPI_TOKEN absent' }}</span>
            </li>
        </ul>
    </div>

    {{-- Adresse de référence --}}
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-6">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Point de référence (bonus proximité)</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-3 text-sm">
            <div class="rounded-lg border border-slate-700 bg-slate-900/50 p-3">
                <p class="text-[10px] uppercase tracking-wider text-zinc-500">Latitude</p>
                <p class="font-mono text-zinc-100">{{ $home['lat'] ?? '—' }}</p>
            </div>
            <div class="rounded-lg border border-slate-700 bg-slate-900/50 p-3">
                <p class="text-[10px] uppercase tracking-wider text-zinc-500">Longitude</p>
                <p class="font-mono text-zinc-100">{{ $home['long'] ?? '—' }}</p>
            </div>
            <div class="rounded-lg border border-slate-700 bg-slate-900/50 p-3">
                <p class="text-[10px] uppercase tracking-wider text-zinc-500">Rayon</p>
                <p class="font-mono text-zinc-100">{{ $home['radius_km'] ?? 30 }} km</p>
            </div>
        </div>
        @if (empty($home['lat']) || empty($home['long']))
            <p class="mt-3 text-xs text-amber-400">
                Renseignez <code class="rounded bg-zinc-950 px-1 py-0.5">BRIGHTSHELL_PROSPECTS_HOME_LAT</code> et
                <code class="rounded bg-zinc-950 px-1 py-0.5">BRIGHTSHELL_PROSPECTS_HOME_LONG</code> pour activer le bonus de proximité géographique.
            </p>
        @endif
    </div>

    {{-- Seuils bandes --}}
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-6">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Seuils de classification</h2>
        <div class="mt-3 grid gap-2 sm:grid-cols-4 text-sm">
            <div class="rounded-lg border border-red-500/20 bg-red-500/5 p-3"><p class="text-[10px] uppercase tracking-wider text-red-400">Hot</p><p class="text-lg font-bold text-red-300 tabular-nums">≥ {{ $scoring['bands']['hot'] ?? 120 }}</p></div>
            <div class="rounded-lg border border-orange-500/20 bg-orange-500/5 p-3"><p class="text-[10px] uppercase tracking-wider text-orange-400">Priority</p><p class="text-lg font-bold text-orange-300 tabular-nums">≥ {{ $scoring['bands']['priority'] ?? 80 }}</p></div>
            <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3"><p class="text-[10px] uppercase tracking-wider text-emerald-400">Standard</p><p class="text-lg font-bold text-emerald-300 tabular-nums">≥ {{ $scoring['bands']['standard'] ?? 50 }}</p></div>
            <div class="rounded-lg border border-slate-700 bg-slate-900/50 p-3"><p class="text-[10px] uppercase tracking-wider text-zinc-400">Watch</p><p class="text-lg font-bold text-zinc-200 tabular-nums">≥ {{ $scoring['bands']['watch'] ?? 25 }}</p></div>
        </div>
    </div>

    {{-- Multiplicateurs --}}
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-6">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Multiplicateurs non linéaires</h2>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach ($modifiers as $key => $mod)
                @if (is_array($mod))
                    <li class="flex items-start justify-between gap-3 rounded-lg border border-slate-700 bg-slate-900/40 p-3">
                        <div>
                            <p class="font-semibold text-zinc-100">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                            <p class="mt-0.5 text-xs text-zinc-500">{{ json_encode($mod, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</p>
                        </div>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>
