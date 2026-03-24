@extends('layouts.admin')

@section('title', 'API sortantes')
@section('topbar_label', 'API sortantes')

@section('content')
    <div class="space-y-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Intégrations</p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Modules API sortantes</h1>
                <p class="mt-2 max-w-2xl text-sm text-zinc-400">
                    Le serveur appelle des URLs externes (météo, webhooks, etc.). Affichage sur le tableau de bord dans la section
                    <strong class="text-zinc-300">API perso</strong> — à la demande ou mis en cache selon un intervalle (cron).
                </p>
            </div>
            <a href="{{ route('admin.outbound-api-widgets.create') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                Nouveau module
            </a>
        </header>

        @include('layouts.partials.flash')

        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Modules</h2>
            </div>
            @if ($widgets->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-zinc-500">
                    Aucun module. Créez-en un pour l’afficher sur le tableau de bord.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[48rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                                <th class="px-5 py-3">Titre</th>
                                <th class="px-5 py-3">Méthode</th>
                                <th class="px-5 py-3">Mode</th>
                                <th class="px-5 py-3">Actif</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                            @foreach ($widgets as $w)
                                <tr class="align-top transition hover:bg-zinc-800/30">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-white">{{ $w->title }}</p>
                                        <p class="mt-0.5 max-w-md truncate font-mono text-xs text-zinc-500" title="{{ $w->url }}">{{ $w->url }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded border border-zinc-700 bg-zinc-950 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-sky-400">{{ $w->http_method }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-zinc-400">
                                        @if ($w->fetch_mode === \App\Models\AdminOutboundApiWidget::FETCH_LIVE)
                                            À la demande
                                        @else
                                            Planifié ({{ $w->cron_interval_minutes }} min)
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-xs">
                                        @if ($w->is_enabled)
                                            <span class="text-emerald-400/90">Oui</span>
                                        @else
                                            <span class="text-zinc-500">Non</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('admin.outbound-api-widgets.edit', $w) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Modifier</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($widgets->hasPages())
                    <div class="border-t border-zinc-800 px-5 py-4">
                        {{ $widgets->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
