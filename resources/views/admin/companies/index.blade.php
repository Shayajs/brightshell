@extends('layouts.admin')
@section('title', 'Sociétés')
@section('topbar_label', 'Sociétés')

@push('topbar_extra')
    <a href="{{ route('admin.companies.create') }}"
        class="flex items-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle société
    </a>
@endpush

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-white">Sociétés</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $companies->total() }} société(s) — reliez un membre à une société pour enrichir les stats.</p>
    </div>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[38rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Société</th>
                        <th class="px-5 py-3">SIRET</th>
                        <th class="px-5 py-3">Membres liés</th>
                        <th class="px-5 py-3">Factures</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($companies as $company)
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-zinc-100">{{ $company->name }}</p>
                                @if ($company->city)
                                    <p class="text-xs text-zinc-500">{{ $company->city }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">{{ $company->siret ?: '—' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($company->users as $u)
                                        <span class="inline-flex rounded-md border border-zinc-700 bg-zinc-800/50 px-2 py-0.5 text-[11px] text-zinc-400">{{ $u->name }}</span>
                                    @empty
                                        <span class="text-xs text-zinc-600">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ $company->invoices_count }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.companies.show', $company) }}"
                                        class="rounded-lg border border-zinc-700 bg-zinc-800/40 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300">
                                        Voir →
                                    </a>
                                    <a href="{{ route('admin.companies.edit', $company) }}"
                                        class="rounded-lg border border-zinc-700 px-3 py-1.5 text-xs text-zinc-500 transition hover:text-zinc-300">
                                        Modifier
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-zinc-600">Aucune société. <a href="{{ route('admin.companies.create') }}" class="text-indigo-400 hover:underline">Créer la première →</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($companies->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4">{{ $companies->links() }}</div>
        @endif
    </div>
</div>
@endsection
