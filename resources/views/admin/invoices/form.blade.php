@extends('layouts.admin')
@section('title', $invoice ? 'Modifier '.$invoice->number : 'Nouvelle facture')
@section('topbar_label', $invoice ? 'Modifier la facture' : 'Nouvelle facture')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.invoices.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Factures</a>
        @if ($invoice)
            <span class="text-zinc-700">/</span>
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-sm text-zinc-500 hover:text-indigo-400">{{ $invoice->number }}</a>
            <span class="text-zinc-700">/</span>
            <span class="text-sm text-zinc-300">Modifier</span>
        @endif
    </div>

    @include('layouts.partials.flash')

    <div class="mx-auto max-w-2xl">
        <form
            method="POST"
            action="{{ $invoice ? route('admin.invoices.update', $invoice) : route('admin.invoices.store') }}"
            class="space-y-6"
        >
            @csrf
            @if ($invoice) @method('PUT') @endif

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Facture</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    @include('layouts.partials.form-field', ['name' => 'number', 'label' => 'N° *', 'type' => 'text', 'value' => old('number', $nextNumber), 'required' => true, 'placeholder' => 'BS-2026-001'])
                    <div>
                        <label for="company_id" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Société</label>
                        <select id="company_id" name="company_id"
                            class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                            <option value="">— Aucune —</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->id }}" @selected(old('company_id', $invoice?->company_id) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'label', 'label' => 'Objet', 'type' => 'text', 'value' => old('label', $invoice?->label), 'placeholder' => 'Développement site web…'])
                    </div>
                    @include('layouts.partials.form-field', ['name' => 'amount_ht', 'label' => 'Montant HT (€) *', 'type' => 'number', 'value' => old('amount_ht', $invoice?->amount_ht), 'required' => true, 'placeholder' => '1500.00'])
                    @include('layouts.partials.form-field', ['name' => 'tva_rate', 'label' => 'TVA (%)', 'type' => 'number', 'value' => old('tva_rate', $invoice?->tva_rate), 'placeholder' => 'Vide = micro (sans TVA)'])
                    <div>
                        <label for="status" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Statut *</label>
                        <select id="status" name="status"
                            class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                            @foreach (['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'cancelled' => 'Annulée'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('status', $invoice?->status ?? 'draft') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Dates</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    @include('layouts.partials.form-field', ['name' => 'issued_at', 'label' => 'Émission', 'type' => 'date', 'value' => old('issued_at', $invoice?->issued_at?->format('Y-m-d'))])
                    @include('layouts.partials.form-field', ['name' => 'due_at', 'label' => 'Échéance', 'type' => 'date', 'value' => old('due_at', $invoice?->due_at?->format('Y-m-d'))])
                    @include('layouts.partials.form-field', ['name' => 'paid_at', 'label' => 'Paiement reçu', 'type' => 'date', 'value' => old('paid_at', $invoice?->paid_at?->format('Y-m-d'))])
                </div>
                <div>
                    <label for="notes" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes internes</label>
                    <textarea id="notes" name="notes" rows="2"
                        class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                    >{{ old('notes', $invoice?->notes) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.invoices.index') }}"
                    class="rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-medium text-zinc-400 transition hover:text-zinc-200">
                    Annuler
                </a>
                <button type="submit"
                    class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                    {{ $invoice ? 'Enregistrer' : 'Créer la facture' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
