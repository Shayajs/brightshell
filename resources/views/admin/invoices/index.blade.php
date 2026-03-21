@extends('layouts.admin')
@section('title', 'Factures')
@section('topbar_label', 'Factures')

@push('topbar_extra')
    <a href="{{ route('admin.invoices.create') }}"
        class="flex items-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle facture
    </a>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">Factures</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $invoices->total() }} facture(s)</p>
        </div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 px-5 py-3 text-right ring-1 ring-white/5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">CA encaissé (toutes années)</p>
            <p class="font-display text-xl font-bold text-emerald-400">{{ number_format($totalPaid, 2, ',', ' ') }} €</p>
        </div>
    </div>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">N°</th>
                        <th class="px-5 py-3">Objet</th>
                        <th class="px-5 py-3">Société</th>
                        <th class="px-5 py-3">Montant HT</th>
                        <th class="px-5 py-3">Statut</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($invoices as $invoice)
                    @php
                        $sc = match($invoice->status) { 'paid' => 'emerald', 'sent' => 'indigo', 'cancelled' => 'red', default => 'zinc' };
                    @endphp
                    <tr class="transition hover:bg-zinc-800/30">
                        <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">{{ $invoice->number }}</td>
                        <td class="px-5 py-3.5 text-zinc-200">{{ $invoice->label ?: '—' }}</td>
                        <td class="px-5 py-3.5 text-zinc-400">
                            @if ($invoice->company)
                                <a href="{{ route('admin.companies.show', $invoice->company) }}" class="hover:text-indigo-400">{{ $invoice->company->name }}</a>
                            @else
                                <span class="text-zinc-600">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-medium text-zinc-100">{{ number_format($invoice->amount_ht, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex rounded-md border border-{{ $sc }}-500/30 bg-{{ $sc }}-500/10 px-2 py-0.5 text-[11px] font-semibold text-{{ $sc }}-300">{{ $invoice->statusLabel() }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-zinc-500">{{ $invoice->issued_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.invoices.show', $invoice) }}"
                                class="rounded-lg border border-zinc-700 bg-zinc-800/40 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300">
                                Voir →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-zinc-600">
                            Aucune facture. <a href="{{ route('admin.invoices.create') }}" class="text-indigo-400 hover:underline">Créer la première →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
@endsection
