@extends('layouts.admin')
@php
    $pageTitle = $pageTitle ?? 'Membres';
    $membersIndexRoute = $membersIndexRoute ?? 'admin.members.index';
@endphp
@section('title', $pageTitle)
@section('topbar_label', $pageTitle)

@push('topbar_extra')
    @if ($showMemberCreate ?? true)
    <a href="{{ route('admin.members.create') }}"
        class="flex items-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau membre
    </a>
    @endif
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">{{ $pageTitle }}</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $members->total() }} compte(s) —
                @if (($status ?? 'active') === 'active')
                    actifs
                @elseif (($status ?? '') === 'archived')
                    archivés
                @else
                    tous
                @endif
                @if (! empty($pageSubtitle))
                    — {{ $pageSubtitle }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route($membersIndexRoute, ['status' => 'active']) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($status ?? 'active') === 'active' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Actifs</a>
            <a href="{{ route($membersIndexRoute, ['status' => 'archived']) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($status ?? '') === 'archived' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Archivés</a>
            <a href="{{ route($membersIndexRoute, ['status' => 'all']) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($status ?? '') === 'all' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Tous</a>
        </div>
    </div>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Nom</th>
                        <th class="px-5 py-3">E-mail</th>
                        <th class="px-5 py-3">Rôles</th>
                        <th class="px-5 py-3">Admin</th>
                        <th class="px-5 py-3">Inscrit le</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($members as $member)
                        <tr class="transition hover:bg-zinc-800/30 {{ $member->trashed() ? 'opacity-80' : '' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @include('partials.user-avatar', ['user' => $member, 'size' => 'h-8 w-8', 'textSize' => 'text-xs'])
                                    <span class="font-medium text-zinc-100">{{ $member->name }}</span>
                                    @if ($member->trashed())
                                        <span class="rounded-md border border-zinc-600 bg-zinc-800/80 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Archivé</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ $member->archived_email ?? $member->email }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($member->roles as $role)
                                        <span class="inline-flex rounded-md border border-indigo-500/30 bg-indigo-500/10 px-2 py-0.5 text-[11px] font-semibold text-indigo-300">{{ $role->slug }}</span>
                                    @empty
                                        <span class="text-xs text-zinc-600">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($member->is_admin)
                                    <span class="inline-flex rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[11px] font-semibold text-amber-300">Admin</span>
                                @else
                                    <span class="text-xs text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-zinc-500">{{ $member->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.members.show', $member) }}"
                                    class="rounded-lg border border-zinc-700 bg-zinc-800/40 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300">
                                    Gérer →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-zinc-600">
                                @if ($pageTitle === 'Clients')
                                    Aucun compte avec le rôle client.
                                    <a href="{{ route('admin.members.create') }}" class="ml-2 text-indigo-400 hover:underline">Créer un membre →</a>
                                @else
                                    Aucun membre pour l'instant.
                                    <a href="{{ route('admin.members.create') }}" class="ml-2 text-indigo-400 hover:underline">Créer le premier →</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($members->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4 text-sm text-zinc-500">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
