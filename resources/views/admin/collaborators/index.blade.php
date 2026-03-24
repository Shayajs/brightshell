@extends('layouts.admin')
@section('title', 'Collaborateurs')
@section('topbar_label', 'Collaborateurs')

@push('topbar_extra')
    <a href="{{ route('admin.collaborator-teams.index') }}"
       class="flex items-center gap-2 rounded-lg border border-zinc-600 bg-zinc-800/50 px-3 py-2 text-xs font-semibold text-zinc-200 transition hover:border-indigo-500/40 hover:text-indigo-300">
        Groupes & accès
    </a>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">Collaborateurs</h1>
            <p class="mt-1 text-sm text-zinc-500">
                Comptes avec accès au portail collaborateurs (rôle <span class="text-zinc-400">admin</span> ou <span class="text-zinc-400">collaborator</span>, ou admin plateforme).
                Les <strong class="text-zinc-400">groupes</strong> définissent des accès qui se <strong class="text-zinc-400">cumulent</strong> si la personne est dans plusieurs groupes — à gérer sous
                <a href="{{ route('admin.collaborator-teams.index') }}" class="text-indigo-400 hover:underline">Groupes collaborateurs</a>.
                — {{ $collaborators->total() }} résultat(s)
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.collaborators.index', array_filter(['status' => 'active', 'team' => $teamId, 'q' => $q])) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($status ?? 'active') === 'active' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Actifs</a>
            <a href="{{ route('admin.collaborators.index', array_filter(['status' => 'archived', 'team' => $teamId, 'q' => $q])) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($status ?? '') === 'archived' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Archivés</a>
            <a href="{{ route('admin.collaborators.index', array_filter(['status' => 'all', 'team' => $teamId, 'q' => $q])) }}"
               class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($status ?? '') === 'all' ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">Tous</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.collaborators.index') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 ring-1 ring-white/5">
        <input type="hidden" name="status" value="{{ $status ?? 'active' }}">
        <div class="min-w-[12rem] flex-1">
            <label for="collab-q" class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Recherche</label>
            <input type="search" name="q" id="collab-q" value="{{ $q }}"
                   placeholder="Nom ou e-mail…"
                   class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
        </div>
        <div class="min-w-[10rem]">
            <label for="collab-team" class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Équipe</label>
            <select name="team" id="collab-team"
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                <option value="">Toutes</option>
                @foreach ($teams as $t)
                    <option value="{{ $t->id }}" @selected((string) ($teamId ?? '') === (string) $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500">Filtrer</button>
        @if (($q ?? '') !== '' || ($teamId ?? '') !== '')
            <a href="{{ route('admin.collaborators.index', ['status' => $status ?? 'active']) }}" class="text-xs font-semibold text-zinc-500 hover:text-indigo-400">Réinitialiser</a>
        @endif
    </form>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Nom</th>
                        <th class="px-5 py-3">E-mail</th>
                        <th class="px-5 py-3">Rôles</th>
                        <th class="px-5 py-3">Équipes</th>
                        <th class="px-5 py-3">Coord.</th>
                        <th class="px-5 py-3">Admin</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($collaborators as $user)
                        <tr class="transition hover:bg-zinc-800/30 {{ $user->trashed() ? 'opacity-80' : '' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @include('partials.user-avatar', ['user' => $user, 'size' => 'h-8 w-8', 'textSize' => 'text-xs'])
                                    <span class="font-medium text-zinc-100">{{ $user->name }}</span>
                                    @if ($user->trashed())
                                        <span class="rounded-md border border-zinc-600 bg-zinc-800/80 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Archivé</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ $user->archived_email ?? $user->email }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($user->roles as $role)
                                        <span class="inline-flex rounded-md border border-indigo-500/30 bg-indigo-500/10 px-2 py-0.5 text-[11px] font-semibold text-indigo-300">{{ $role->slug }}</span>
                                    @empty
                                        <span class="text-xs text-zinc-600">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex max-w-xs flex-wrap gap-1">
                                    @forelse ($user->collaboratorTeams as $team)
                                        <span class="inline-flex rounded-md border border-emerald-500/25 bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-300" title="{{ $team->pivot->is_team_manager ? 'Gérant d’équipe' : 'Membre' }}">
                                            {{ $team->name }}
                                            @if ($team->pivot->is_team_manager)
                                                <span class="ml-0.5 text-emerald-400/90">★</span>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-xs text-zinc-600">Aucune</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($user->can_manage_collaborator_team_managers)
                                    <span class="text-xs font-medium text-amber-300">Oui</span>
                                @else
                                    <span class="text-xs text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($user->is_admin)
                                    <span class="inline-flex rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[11px] font-semibold text-amber-300">Plateforme</span>
                                @else
                                    <span class="text-xs text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.members.show', $user) }}"
                                    class="rounded-lg border border-zinc-700 bg-zinc-800/40 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300">
                                    Gérer →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-zinc-600">
                                Aucun collaborateur pour ces critères.
                                <a href="{{ route('admin.members.index') }}" class="ml-2 text-indigo-400 hover:underline">Membres →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($collaborators->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4 text-sm text-zinc-500">
                {{ $collaborators->links() }}
            </div>
        @endif
    </div>

    <p class="text-xs text-zinc-600">
        Affectation aux groupes et rôle coordinateur : fiche membre (« Collaborateurs — équipes ») ou
        <a href="{{ route('admin.collaborator-teams.index') }}" class="text-indigo-400/90 hover:underline">page des groupes</a> (membres par groupe).
    </p>
</div>
@endsection
