@extends('layouts.admin')
@section('title', $invoice->number)
@section('topbar_label', 'Facture')

@push('topbar_extra')
    <a href="{{ route('admin.invoices.edit', $invoice) }}"
        class="flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-xs font-semibold text-zinc-300 transition hover:border-zinc-600 hover:text-white">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Modifier
    </a>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.invoices.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Factures</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm font-mono text-zinc-300">{{ $invoice->number }}</span>
    </div>

    @include('layouts.partials.flash')

    @php $sc = match($invoice->status) { 'paid' => 'emerald', 'sent' => 'indigo', 'cancelled' => 'red', default => 'zinc' }; @endphp

    <div class="mx-auto max-w-2xl rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="flex items-center justify-between border-b border-zinc-800 px-6 py-5">
            <div>
                <p class="font-mono text-xs text-zinc-500">{{ $invoice->number }}</p>
                <h1 class="font-display text-xl font-bold text-white">{{ $invoice->label ?: 'Facture sans objet' }}</h1>
            </div>
            <span class="inline-flex rounded-lg border border-{{ $sc }}-500/30 bg-{{ $sc }}-500/10 px-3 py-1.5 text-sm font-semibold text-{{ $sc }}-300">{{ $invoice->statusLabel() }}</span>
        </div>

        <dl class="divide-y divide-zinc-800/60 px-6 py-2 text-sm">
            @if ($invoice->company)
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">Société</dt>
                <dd><a href="{{ route('admin.companies.show', $invoice->company) }}" class="font-medium text-indigo-400 hover:underline">{{ $invoice->company->name }}</a></dd>
            </div>
            @endif
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">Montant HT</dt>
                <dd class="font-display text-lg font-bold text-white">{{ number_format($invoice->amount_ht, 2, ',', ' ') }} €</dd>
            </div>
            @if ($invoice->tva_rate !== null)
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">TVA ({{ $invoice->tva_rate }}%)</dt>
                <dd class="text-zinc-300">{{ number_format($invoice->amountTtc() - $invoice->amount_ht, 2, ',', ' ') }} €</dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">Total TTC</dt>
                <dd class="font-semibold text-zinc-100">{{ number_format($invoice->amountTtc(), 2, ',', ' ') }} €</dd>
            </div>
            @else
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">TVA</dt>
                <dd class="text-zinc-500">Micro-entreprise — TVA non applicable</dd>
            </div>
            @endif
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">Émise le</dt>
                <dd class="text-zinc-300">{{ $invoice->issued_at?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">Échéance</dt>
                <dd class="text-zinc-300">{{ $invoice->due_at?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            @if ($invoice->paid_at)
            <div class="flex justify-between py-3">
                <dt class="text-zinc-500">Payée le</dt>
                <dd class="font-medium text-emerald-400">{{ $invoice->paid_at->format('d/m/Y') }}</dd>
            </div>
            @endif
            @if ($invoice->notes)
            <div class="py-3">
                <dt class="mb-2 text-zinc-500">Notes</dt>
                <dd class="rounded-lg border border-zinc-800 bg-zinc-950/50 p-3 text-zinc-400">{{ $invoice->notes }}</dd>
            </div>
            @endif
        </dl>

        <div class="flex justify-end gap-3 border-t border-zinc-800 px-6 py-4">
            <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}"
                onsubmit="return confirm('Archiver cette facture ?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="rounded-lg border border-red-500/30 px-4 py-2 text-xs font-semibold text-red-400 transition hover:bg-red-500/10">
                    Archiver
                </button>
            </form>
            <a href="{{ route('admin.invoices.edit', $invoice) }}"
                class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">
                Modifier
            </a>
        </div>
    </div>
</div>
@endsection
