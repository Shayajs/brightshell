@extends('layouts.admin')

@section('title', $team->name)
@section('topbar_label', 'Portail collaborateurs')
@section('portal_main_max', 'max-w-none')

@section('content')
    @php
        $u = auth()->user();
    @endphp
    <div class="space-y-8">
        <div class="flex flex-wrap items-center gap-3 text-sm">
            <a href="{{ route('portals.collabs.teams.index') }}" class="text-zinc-500 hover:text-indigo-400">← Équipes</a>
            <span class="text-zinc-700">/</span>
            <span class="text-zinc-300">{{ $team->name }}</span>
        </div>

        @include('layouts.partials.flash')

        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $team->name }}</h1>
                @if ($team->is_admin_team)
                    <p class="mt-2 text-xs text-amber-400/90">Équipe d’administration — les permissions de cette équipe ne peuvent être modifiées que par un admin système.</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @can('view', $team)
                    <a href="{{ route('portals.collabs.teams.messages', $team) }}"
                       class="inline-flex items-center rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-4 py-2 text-sm font-semibold text-indigo-300 transition hover:bg-indigo-500/20">
                        Messagerie
                    </a>
                    <a href="{{ route('portals.collabs.teams.permissions.edit', $team) }}"
                       class="inline-flex items-center rounded-lg border border-zinc-600 bg-zinc-900 px-4 py-2 text-sm font-semibold text-zinc-200 transition hover:border-zinc-500">
                        Permissions
                    </a>
                @endcan
            </div>
        </header>

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Droits de l’équipe</h2>
            <p class="mt-1 text-xs text-zinc-500">Capabilities actives (héritées par les membres via cette équipe).</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($team->capabilities as $cap)
                    <span class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">{{ $cap->label }}</span>
                @empty
                    <span class="text-sm text-zinc-500">Aucun droit spécifique pour le moment.</span>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Membres</h2>

            @can('addMember', $team)
                <form method="POST" action="{{ route('portals.collabs.teams.members.store', $team) }}" class="mt-4 flex flex-wrap items-end gap-3 border-b border-zinc-800 pb-6">
                    @csrf
                    <div class="min-w-[14rem] flex-1">
                        <label for="invite-email" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Ajouter par e-mail</label>
                        <input type="email" id="invite-email" name="email" value="{{ old('email') }}" required placeholder="collaborateur@…"
                               class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Ajouter</button>
                </form>
            @endcan

            <ul class="mt-4 divide-y divide-zinc-800/80">
                @foreach ($team->users as $member)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                        <div class="flex min-w-0 items-center gap-3">
                            @include('partials.user-avatar', ['user' => $member, 'size' => 'h-10 w-10', 'textSize' => 'text-sm'])
                            <div class="min-w-0">
                                <p class="truncate font-medium text-zinc-100">{{ $member->name }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $member->email }}</p>
                            </div>
                            @if ($member->pivot->is_team_manager ?? false)
                                <span class="shrink-0 rounded-md border border-violet-500/30 bg-violet-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase text-violet-300">Gérant</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @can('setTeamManagerStatus', [$team, $member])
                                <form method="POST" action="{{ route('portals.collabs.teams.members.manager', [$team, $member]) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_team_manager" value="{{ ($member->pivot->is_team_manager ?? false) ? '0' : '1' }}">
                                    <button type="submit" class="rounded-lg border border-zinc-600 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-violet-500/40 hover:text-violet-200">
                                        {{ ($member->pivot->is_team_manager ?? false) ? 'Retirer gérant' : 'Nommer gérant' }}
                                    </button>
                                </form>
                            @endcan
                            @can('removeMember', [$team, $member])
                                @if ($member->id !== $u->id)
                                    <form method="POST" action="{{ route('portals.collabs.teams.members.destroy', [$team, $member]) }}" class="inline" onsubmit="return confirm('Retirer ce membre de l’équipe ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-300 hover:bg-red-500/20">Retirer</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
@endsection
