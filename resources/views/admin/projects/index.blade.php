@extends('layouts.admin')
@section('title', 'Projets clients')
@section('topbar_label', 'Projets clients')

@push('topbar_extra')
    <a href="{{ route('admin.projects.create') }}"
        class="flex items-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau projet
    </a>
@endpush

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-white">Projets clients</h1>
        <p class="mt-1 text-sm text-zinc-500">Gestion des projets du portail <span class="text-zinc-400">project.*</span> — invitations et droits réservés aux administrateurs.</p>
    </div>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Projet</th>
                        <th class="px-5 py-3">Slug</th>
                        <th class="px-5 py-3">Société</th>
                        <th class="px-5 py-3">Membres</th>
                        <th class="px-5 py-3">État</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($projects as $proj)
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-zinc-100">{{ $proj->name }}</p>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">{{ $proj->slug }}</td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ $proj->company?->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ $proj->members_count }}</td>
                            <td class="px-5 py-3.5">
                                @if ($proj->isArchived())
                                    <span class="text-xs text-amber-400/90">Archivé</span>
                                @else
                                    <span class="text-xs text-emerald-400/90">Actif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.projects.edit', $proj) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-zinc-500">Aucun projet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($projects->hasPages())
            <div class="border-t border-zinc-800 px-5 py-3">{{ $projects->links() }}</div>
        @endif
    </div>
</div>
@endsection
