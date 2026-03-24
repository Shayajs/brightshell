@extends('layouts.admin')
@section('title', 'Groupes collaborateurs')
@section('topbar_label', 'Groupes collaborateurs')

@push('topbar_extra')
    <a href="{{ route('admin.collaborator-teams.create') }}"
       class="flex items-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau groupe
    </a>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">Groupes collaborateurs</h1>
            <p class="mt-1 max-w-2xl text-sm text-zinc-500">
                Chaque groupe porte un ensemble d’accès (capabilities). Un même collaborateur peut appartenir à plusieurs groupes : les accès se cumulent.
                Les <a href="{{ route('admin.collaborators.index') }}" class="text-indigo-400 hover:underline">comptes collaborateurs</a> restent gérés dans la liste dédiée et sur chaque fiche membre.
            </p>
        </div>
        <a href="{{ route('admin.collaborators.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-indigo-400">← Liste collaborateurs</a>
    </div>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Groupe</th>
                        <th class="px-5 py-3">Slug</th>
                        <th class="px-5 py-3">Membres</th>
                        <th class="px-5 py-3">Accès</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($teams as $t)
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-zinc-100">{{ $t->name }}</span>
                                @if ($t->is_admin_team)
                                    <span class="ml-2 inline-flex rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase text-amber-300">Admin collab.</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-zinc-500">{{ $t->slug }}</td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ $t->users_count }}</td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ $t->capabilities_count }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.collaborator-teams.edit', $t) }}"
                                       class="rounded-lg border border-zinc-700 bg-zinc-800/40 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300">
                                        Modifier →
                                    </a>
                                    @if (! $t->is_admin_team)
                                        <form method="POST" action="{{ route('admin.collaborator-teams.destroy', $t) }}" class="inline"
                                              onsubmit="return confirm('Supprimer ce groupe ? Les membres perdront uniquement ce groupe (les autres restent).');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-300 hover:bg-red-500/20">
                                                Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-zinc-600">Aucun groupe.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
