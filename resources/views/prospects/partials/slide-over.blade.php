@php
    use App\Services\Prospects\Scoring\ScoreBand;

    $modifiers = (array) ($prospect?->score_breakdown['modifiers'] ?? []);
    $base = (array) ($prospect?->score_breakdown['base'] ?? []);
@endphp

<div>
    <div
        x-data="{ open: @entangle('open').live }"
        x-show="open"
        x-cloak
        @keydown.escape.window="$wire.close()"
        class="fixed inset-0 z-50"
    >
        <div x-show="open" x-transition.opacity
             class="absolute inset-0 bg-black/60 backdrop-blur-sm"
             wire:click="close"
             aria-hidden="true"></div>

        <aside x-show="open"
               x-transition:enter="transition transform ease-out duration-200"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition transform ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="absolute inset-y-0 right-0 flex w-full max-w-2xl flex-col overflow-y-auto border-l border-slate-800 bg-slate-900 shadow-2xl">
            @if ($prospect)
                {{-- ─── En-tête ──────────────────────────────────────────── --}}
                <header class="flex items-start gap-4 border-b border-slate-700 p-6">
                    <x-prospects.company-logo :prospect="$prospect" />
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate font-display text-xl font-bold text-white">{{ $prospect->nom_entreprise }}</h2>
                        <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-400">
                            <span class="tabular-nums">SIREN {{ $prospect->siren }}</span>
                            @if ($prospect->code_naf)
                                <span>·</span>
                                <span class="font-mono">{{ $prospect->code_naf }}</span>
                            @endif
                            @if ($prospect->ville)
                                <span>·</span>
                                <span>{{ $prospect->ville }}</span>
                            @endif
                        </p>
                        <p class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                            <a href="{{ $prospect->fiche_gouv_url }}" target="_blank" rel="noopener" class="text-indigo-400 hover:text-indigo-300">
                                Fiche officielle ↗
                            </a>
                            @if ($prospect->site_internet)
                                <a href="{{ $prospect->site_internet }}" target="_blank" rel="noopener" class="text-cyan-400 hover:text-cyan-300">
                                    Site web ↗
                                </a>
                            @endif
                            @if ($prospect->linkedin_search_url)
                                <a href="{{ $prospect->linkedin_search_url }}" target="_blank" rel="noopener" class="text-blue-400 hover:text-blue-300">
                                    Chercher sur LinkedIn ↗
                                </a>
                            @endif
                        </p>
                    </div>
                    <button type="button" wire:click="close"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-700 text-zinc-400 hover:border-zinc-500 hover:text-zinc-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>

                {{-- ─── Score breakdown ──────────────────────────────────── --}}
                <section class="border-b border-slate-700 p-6">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Pourquoi ce score ?</h3>

                    <div class="mt-4 flex flex-wrap items-center gap-6">
                        <div class="text-center">
                            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Global</p>
                            <p class="text-3xl font-bold {{ $prospect->band()->accent() }}">{{ $prospect->score_global }}</p>
                            <x-prospects.band-badge :prospect="$prospect" />
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Score Web</p>
                            <p class="text-2xl font-bold text-cyan-400">{{ $prospect->score_website }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Score Soft</p>
                            <p class="text-2xl font-bold text-purple-400">{{ $prospect->score_software }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Confiance</p>
                            <p class="text-2xl font-bold text-zinc-200">{{ $prospect->score_confidence }}<span class="text-sm text-zinc-500">/100</span></p>
                        </div>
                    </div>

                    {{-- Radar : 4 axes (NAF, Structure, Gouvernance, Signaux) --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach (['naf' => ['NAF', 30], 'structure' => ['Structure', 30], 'gouvernance' => ['Gouvernance', 15], 'signaux' => ['Signaux', 25]] as $key => [$label, $max])
                            @php $val = (int) ($base[$key] ?? 0); $pct = $max > 0 ? min(100, (int) round(($val / $max) * 100)) : 0; @endphp
                            <div class="rounded-lg border border-slate-700 bg-slate-800/40 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-zinc-500">{{ $label }}</p>
                                <p class="mt-1 text-lg font-bold text-zinc-100 tabular-nums">{{ $val }}<span class="text-xs text-zinc-500">/{{ $max }}</span></p>
                                <div class="mt-1 h-1 overflow-hidden rounded bg-zinc-800">
                                    <div class="h-full rounded bg-indigo-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($modifiers !== [])
                        <div class="mt-4 space-y-2">
                            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Multiplicateurs déclenchés</p>
                            @foreach ($modifiers as $key => $mod)
                                @php
                                    $isVeto = $key === 'veto.procedure_collective';
                                    $mult = (float) ($mod['multiplier'] ?? 1);
                                    $flat = (int) ($mod['flat_bonus'] ?? 0);
                                @endphp
                                <div class="flex items-start gap-3 rounded-lg border {{ $isVeto ? 'border-red-500/40 bg-red-500/10' : 'border-indigo-500/20 bg-indigo-500/5' }} p-3">
                                    <span class="inline-flex shrink-0 items-center rounded-md px-2 py-0.5 text-xs font-bold {{ $isVeto ? 'bg-red-500/20 text-red-300' : 'bg-indigo-500/15 text-indigo-300' }}">
                                        @if ($isVeto)
                                            VÉTO ×0
                                        @elseif ($mult !== 1.0)
                                            ×{{ rtrim(rtrim(number_format($mult, 2), '0'), '.') }}
                                        @else
                                            +{{ $flat }}
                                        @endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-zinc-100">{{ str_replace(['_', '.'], [' ', ' / '], $key) }}</p>
                                        <p class="mt-0.5 text-xs text-zinc-400">{{ $mod['why'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- ─── Finances ─────────────────────────────────────────── --}}
                @if ($prospect->chiffre_affaires || $prospect->resultat_net)
                    <section class="border-b border-slate-700 p-6">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Finances</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border border-slate-700 bg-slate-800/40 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-zinc-500">Exercice</p>
                                <p class="text-lg font-bold text-zinc-100">{{ $prospect->exercice_bilan ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-700 bg-slate-800/40 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-zinc-500">Chiffre d'affaires</p>
                                <p class="text-lg font-bold tabular-nums text-zinc-100">
                                    {{ $prospect->chiffre_affaires !== null ? number_format($prospect->chiffre_affaires, 0, ',', ' ') . ' €' : '—' }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-slate-700 bg-slate-800/40 p-3">
                                <p class="text-[10px] uppercase tracking-wider text-zinc-500">Résultat net</p>
                                <p class="text-lg font-bold tabular-nums {{ ($prospect->resultat_net ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $prospect->resultat_net !== null ? number_format($prospect->resultat_net, 0, ',', ' ') . ' €' : '—' }}
                                </p>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- ─── Timeline BODACC ──────────────────────────────────── --}}
                @if ($prospect->bodacc_events && count($prospect->bodacc_events) > 0)
                    <section class="border-b border-slate-700 p-6">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Événements BODACC</h3>
                        <ol class="mt-3 space-y-3 border-l border-slate-700 pl-4">
                            @foreach (array_slice($prospect->bodacc_events, 0, 8) as $event)
                                <li class="relative">
                                    <span class="absolute -left-[21px] top-1 h-2 w-2 rounded-full bg-indigo-400 ring-2 ring-slate-900"></span>
                                    <p class="text-xs text-zinc-500 tabular-nums">{{ $event['date'] ?? '—' }}</p>
                                    <p class="mt-0.5 text-sm font-semibold text-zinc-200">{{ str_replace('_', ' ', ucfirst($event['type'] ?? 'événement')) }}</p>
                                    <p class="mt-0.5 line-clamp-2 text-xs text-zinc-400">{{ $event['libelle'] ?? '' }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                {{-- ─── Carte Leaflet ────────────────────────────────────── --}}
                @if ($prospect->latitude && $prospect->longitude)
                    <section class="border-b border-slate-700 p-6">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Localisation</h3>
                        <div class="mt-3 overflow-hidden rounded-lg border border-slate-700">
                            <div wire:ignore
                                 x-data
                                 x-init="(() => {
                                    const id = 'map-{{ $prospect->id }}';
                                    if (!window.L || document.getElementById(id)._leaflet_id) return;
                                    const map = L.map(id, { zoomControl: true, attributionControl: true }).setView([{{ $prospect->latitude }}, {{ $prospect->longitude }}], 13);
                                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '© OpenStreetMap' }).addTo(map);
                                    L.marker([{{ $prospect->latitude }}, {{ $prospect->longitude }}]).addTo(map).bindPopup({!! json_encode($prospect->nom_entreprise) !!});
                                    @if ($homeLat && $homeLong)
                                        L.circle([{{ $homeLat }}, {{ $homeLong }}], { radius: {{ (int) ($homeRadius ?? 30) * 1000 }}, color: '#10b981', weight: 1, fillOpacity: 0.05 }).addTo(map);
                                    @endif
                                 })()"
                                 id="map-{{ $prospect->id }}"
                                 class="h-56 w-full"></div>
                        </div>
                        @if ($prospect->distance_km_home !== null)
                            <p class="mt-2 text-xs text-zinc-400">
                                Distance home : <span class="font-semibold {{ $prospect->distance_km_home <= (int) ($homeRadius ?? 30) ? 'text-emerald-400' : 'text-zinc-300' }}">{{ $prospect->distance_km_home }} km</span>
                            </p>
                        @endif
                    </section>
                @endif

                {{-- ─── Actions ──────────────────────────────────────────── --}}
                <footer class="mt-auto flex items-center justify-end gap-2 border-t border-slate-700 p-4">
                    @if (! $prospect->traite)
                        <button type="button" wire:click="marquerTraite"
                                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>
                            Marquer comme traité
                        </button>
                    @else
                        <span class="text-xs text-zinc-500">Marqué traité le {{ $prospect->traite_at?->format('d/m/Y H:i') }}</span>
                    @endif
                </footer>
            @endif
        </aside>
    </div>
</div>
