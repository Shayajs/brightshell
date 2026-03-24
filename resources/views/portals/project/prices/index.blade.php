@extends('layouts.admin')

@section('title', 'Prix & devis — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Prix & devis (projet)</h1>
        <p class="mt-1 text-sm text-zinc-500">Hors factures légales — voir module facturation pour les montants comptables.</p>
    </header>

    @can('update', $project)
        <form method="POST" action="{{ route('portals.project.prices.store', $project) }}" class="grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 sm:grid-cols-2 lg:grid-cols-5">
            @csrf
            <div class="lg:col-span-2">
                <label class="text-xs text-zinc-500">Libellé</label>
                <input type="text" name="label" required class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-xs text-zinc-500">Qté</label>
                <input type="text" name="quantity" value="1" required class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-xs text-zinc-500">PU HT</label>
                <input type="text" name="unit_price_ht" required placeholder="0" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-xs text-zinc-500">TVA %</label>
                <input type="text" name="vat_rate" placeholder="20" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            </div>
            <div class="sm:col-span-2 lg:col-span-5">
                <button type="submit" class="rounded-lg bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Ajouter la ligne</button>
            </div>
        </form>
    @endcan

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-800 text-[10px] font-semibold uppercase text-zinc-500">
                <tr>
                    <th class="px-4 py-3">Libellé</th>
                    <th class="px-4 py-3">Qté</th>
                    <th class="px-4 py-3">PU HT</th>
                    <th class="px-4 py-3">TVA</th>
                    <th class="px-4 py-3">Total HT</th>
                    <th class="px-4 py-3">Total TTC</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/60">
                @foreach ($items as $item)
                    <tr>
                        <td class="px-4 py-3 text-zinc-200">{{ $item->label }}</td>
                        <td class="px-4 py-3 text-zinc-400">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-zinc-400">{{ number_format((float) $item->unit_price_ht, 4, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-zinc-400">{{ $item->vat_rate !== null ? $item->vat_rate.' %' : '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-zinc-300">{{ number_format($item->lineTotalHt(), 2, ',', ' ') }} €</td>
                        <td class="px-4 py-3 font-mono text-xs text-zinc-300">{{ number_format($item->lineTotalTtc(), 2, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $project)
                                <details class="inline text-left">
                                    <summary class="cursor-pointer text-xs text-cyan-400">Éditer</summary>
                                    <form method="POST" action="{{ route('portals.project.prices.update', [$project, $item]) }}" class="mt-2 space-y-2 rounded border border-zinc-700 bg-zinc-950 p-3">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="label" value="{{ $item->label }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="text" name="quantity" value="{{ $item->quantity }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="text" name="unit_price_ht" value="{{ $item->unit_price_ht }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <input type="text" name="vat_rate" value="{{ $item->vat_rate }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                        <button type="submit" class="text-xs font-semibold text-cyan-400">OK</button>
                                    </form>
                                    <form method="POST" action="{{ route('portals.project.prices.destroy', [$project, $item]) }}" class="mt-2" onsubmit="return confirm('Supprimer ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400">Supprimer</button>
                                    </form>
                                </details>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap justify-end gap-8 rounded-2xl border border-zinc-800 bg-zinc-950/50 p-5 text-sm">
        <div>
            <p class="text-xs uppercase tracking-wide text-zinc-500">Total HT</p>
            <p class="mt-1 font-mono text-xl font-bold text-white">{{ number_format($totalHt, 2, ',', ' ') }} €</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-zinc-500">Total TTC (estim.)</p>
            <p class="mt-1 font-mono text-xl font-bold text-cyan-200">{{ number_format($totalTtc, 2, ',', ' ') }} €</p>
        </div>
    </div>
</div>
@endsection
