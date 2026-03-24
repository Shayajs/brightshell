@extends('layouts.admin')
@section('title', $team->name.' — groupe')
@section('topbar_label', 'Groupes collaborateurs')

@section('content')
<div class="space-y-8">
    <div class="flex flex-wrap items-center gap-3 text-sm">
        <a href="{{ route('admin.collaborator-teams.index') }}" class="text-zinc-500 hover:text-indigo-400">← Groupes</a>
        <span class="text-zinc-700">/</span>
        <span class="text-zinc-300">{{ $team->name }}</span>
    </div>

    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">{{ $team->name }}</h1>
        @if ($team->is_admin_team)
            <p class="mt-2 text-sm text-amber-400/90">Équipe d’administration collaborateurs — ne peut pas être supprimée.</p>
        @endif
    </header>

    <form method="POST" action="{{ route('admin.collaborator-teams.update', $team) }}" class="space-y-8 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="name" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nom affiché</label>
                <input type="text" name="name" id="name" value="{{ old('name', $team->name) }}" required maxlength="128"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $team->slug) }}" required maxlength="64"
                       pattern="[a-z0-9]+(-[a-z0-9]+)*"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                @error('slug')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3">
                <input type="hidden" name="is_admin_team" value="0">
                <input type="checkbox" name="is_admin_team" value="1" @checked(old('is_admin_team', $team->is_admin_team))
                       class="mt-0.5 h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
                <div>
                    <p class="text-sm font-medium text-zinc-100">Équipe d’administration collaborateurs</p>
                    <p class="mt-1 text-xs text-zinc-500">Une seule à la fois. Pour retirer ce statut à ce groupe, une autre équipe doit déjà être marquée administration, ou cochez-en une autre puis enregistrez.</p>
                </div>
            </label>
            @error('is_admin_team')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        @include('admin.collaborator-teams.partials.capability-fields', ['allCapabilities' => $allCapabilities, 'team' => $team])

        <div class="flex justify-end border-t border-zinc-800 pt-6">
            <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Enregistrer le groupe</button>
        </div>
    </form>

    <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Membres du groupe</h2>
        <p class="mt-1 text-xs text-zinc-500">Même logique que le portail collaborateurs : seuls les comptes avec accès portail (rôles / admin plateforme) peuvent être ajoutés.</p>

        <form method="POST" action="{{ route('admin.collaborator-teams.members.store', $team) }}" class="mt-4 flex flex-wrap items-end gap-3 border-b border-zinc-800 pb-6">
            @csrf
            <div class="min-w-[14rem] flex-1">
                <label for="invite-email" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Ajouter par e-mail</label>
                <input type="email" id="invite-email" name="email" value="{{ old('email') }}" required placeholder="collaborateur@…"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Ajouter</button>
        </form>

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
                        <form method="POST" action="{{ route('admin.collaborator-teams.members.manager', [$team, $member]) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_team_manager" value="{{ ($member->pivot->is_team_manager ?? false) ? '0' : '1' }}">
                            <button type="submit" class="rounded-lg border border-zinc-600 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-violet-500/40 hover:text-violet-200">
                                {{ ($member->pivot->is_team_manager ?? false) ? 'Retirer gérant' : 'Nommer gérant' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.members.show', $member) }}" class="rounded-lg border border-zinc-600 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-indigo-500/40 hover:text-indigo-300">Fiche membre</a>
                        <form method="POST" action="{{ route('admin.collaborator-teams.members.destroy', [$team, $member]) }}" class="inline" onsubmit="return confirm('Retirer ce membre du groupe ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-300 hover:bg-red-500/20">Retirer</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</div>
@endsection
