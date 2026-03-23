@extends('layouts.admin')
@section('title', $company->name)
@section('topbar_label', 'Société')

@push('topbar_extra')
    <a href="{{ route('admin.companies.edit', $company) }}"
        class="flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-xs font-semibold text-zinc-300 transition hover:border-zinc-600 hover:text-white">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Modifier
    </a>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.companies.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Sociétés</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">{{ $company->name }}</span>
    </div>

    @include('layouts.partials.flash')

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Infos société --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-4">
            <div class="flex flex-wrap items-start gap-4">
                @if ($company->logoUrl())
                    <img src="{{ $company->logoUrl() }}" alt="" class="h-16 w-16 shrink-0 rounded-xl border border-zinc-800 object-contain bg-zinc-950 p-1">
                @else
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-zinc-800 bg-zinc-950 text-lg font-bold text-zinc-600 font-display" aria-hidden="true">
                        {{ strtoupper(substr($company->name, 0, 1)) }}
                    </div>
                @endif
                <h2 class="font-display text-lg font-bold text-white">{{ $company->name }}</h2>
            </div>
            <dl class="space-y-3 text-sm">
                @if ($company->siret)
                <div class="flex justify-between">
                    <dt class="text-zinc-500">SIRET</dt>
                    <dd class="font-mono text-zinc-200">{{ $company->siret }}</dd>
                </div>
                @endif
                @if ($company->address || $company->city)
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Adresse</dt>
                    <dd class="text-right text-zinc-300">{{ implode(', ', array_filter([$company->address, $company->city])) }}</dd>
                </div>
                @endif
                @if ($company->website)
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Site</dt>
                    <dd><a href="{{ $company->website }}" target="_blank" class="text-indigo-400 hover:underline">{{ parse_url($company->website, PHP_URL_HOST) }}</a></dd>
                </div>
                @endif
                @if ($company->contact_name)
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Contact</dt>
                    <dd class="text-zinc-300">{{ $company->contact_name }}</dd>
                </div>
                @endif
                @if ($company->contact_email)
                <div class="flex justify-between">
                    <dt class="text-zinc-500">E-mail</dt>
                    <dd><a href="mailto:{{ $company->contact_email }}" class="text-indigo-400 hover:underline">{{ $company->contact_email }}</a></dd>
                </div>
                @endif
            </dl>
            @if ($company->notes)
                <div class="rounded-lg border border-zinc-800 bg-zinc-950/50 p-3 text-sm text-zinc-400">{{ $company->notes }}</div>
            @endif
            {{-- Membres liés --}}
            @if ($company->users->isNotEmpty())
            <div class="border-t border-zinc-800 pt-4">
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-600">Membres liés</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($company->users as $u)
                        <a href="{{ route('admin.members.show', $u) }}"
                            class="flex items-center gap-2 rounded-lg border border-zinc-700 px-2.5 py-1.5 text-xs font-medium text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300">
                            @include('partials.user-avatar', ['user' => $u, 'size' => 'h-6 w-6', 'textSize' => 'text-[10px]'])
                            <span>{{ $u->name }}</span>
                            @if ($u->hasRole('client') && $u->pivot->can_manage_company)
                                <span class="rounded border border-amber-500/30 bg-amber-500/10 px-1 py-px text-[9px] font-semibold uppercase tracking-wide text-amber-300">Responsable</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Factures --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Factures</h2>
                <a href="{{ route('admin.invoices.create') }}"
                    class="rounded-lg border border-indigo-500/30 bg-indigo-500/10 px-3 py-1.5 text-xs font-semibold text-indigo-300 transition hover:bg-indigo-500/20">
                    + Nouvelle
                </a>
            </div>
            @if ($company->invoices->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-zinc-600">Aucune facture pour cette société.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-800 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                                <th class="px-5 py-3">N°</th>
                                <th class="px-5 py-3">Objet</th>
                                <th class="px-5 py-3">Montant HT</th>
                                <th class="px-5 py-3">Statut</th>
                                <th class="px-5 py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach ($company->invoices->sortByDesc('issued_at') as $inv)
                            <tr class="transition hover:bg-zinc-800/30">
                                <td class="px-5 py-3 font-mono text-xs text-zinc-400">
                                    <a href="{{ route('admin.invoices.show', $inv) }}" class="hover:text-indigo-400">{{ $inv->number }}</a>
                                </td>
                                <td class="px-5 py-3 text-zinc-300">{{ $inv->label ?: '—' }}</td>
                                <td class="px-5 py-3 text-zinc-200">{{ number_format($inv->amount_ht, 2, ',', ' ') }} €</td>
                                <td class="px-5 py-3">
                                    @php
                                        $sc = match($inv->status) { 'paid' => 'emerald', 'sent' => 'indigo', 'cancelled' => 'red', default => 'zinc' };
                                    @endphp
                                    <span class="inline-flex rounded-md border border-{{ $sc }}-500/30 bg-{{ $sc }}-500/10 px-2 py-0.5 text-[11px] font-semibold text-{{ $sc }}-300">{{ $inv->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3 text-zinc-500">{{ $inv->issued_at?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
