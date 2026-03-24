@extends('layouts.admin')

@php
    $isEdit = $project !== null;
@endphp

@section('title', $isEdit ? 'Modifier le projet' : 'Nouveau projet')
@section('topbar_label', 'Projets clients')

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('admin.projects.index') }}" class="text-indigo-400 hover:text-indigo-300">← Projets</a>
    </p>

    <header>
        <h1 class="font-display text-2xl font-bold text-white">{{ $isEdit ? $project->name : 'Nouveau projet' }}</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $isEdit ? 'Membres, droits et métadonnées.' : 'Création — vous pourrez inviter des membres ensuite.' }}</p>
    </header>

    @include('layouts.partials.flash')

    @if ($errors->any())
        <div class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('admin.projects.update', $project) : route('admin.projects.store') }}" class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="p-name" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Nom</label>
                <input id="p-name" type="text" name="name" value="{{ old('name', $project->name ?? '') }}" required
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
            </div>
            <div>
                <label for="p-slug" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Slug URL (optionnel)</label>
                <input id="p-slug" type="text" name="slug" value="{{ old('slug', $project->slug ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                    placeholder="Généré depuis le nom si vide">
            </div>
            <div>
                <label for="p-company" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Société (optionnel)</label>
                <select id="p-company" name="company_id"
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                    <option value="">—</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" @selected((int) old('company_id', $project->company_id ?? 0) === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="p-desc" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Description</label>
                <textarea id="p-desc" name="description" rows="4"
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">{{ old('description', $project->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">Enregistrer</button>
        </div>
    </form>

    @if ($isEdit)
        <div class="flex flex-wrap gap-3">
            @if ($project->isArchived())
                <form method="POST" action="{{ route('admin.projects.unarchive', $project) }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-emerald-500/40 bg-emerald-600/20 px-4 py-2 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-600/30">Sortir des archives</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.projects.archive', $project) }}" onsubmit="return confirm('Archiver ce projet ? Les membres gardent l’accès.');">
                    @csrf
                    <button type="submit" class="rounded-lg border border-amber-500/40 bg-amber-600/15 px-4 py-2 text-sm font-semibold text-amber-200 transition hover:bg-amber-600/25">Archiver</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Mettre le projet en corbeille ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/20">Corbeille</button>
            </form>
        </div>

        <section class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Membres et droits</h2>
            <p class="text-xs text-zinc-500">Seuls les administrateurs gèrent ces cases. « Voir » est requis pour accéder au projet dans le portail.</p>

            <div class="rounded-xl border border-cyan-500/20 bg-cyan-500/5 p-4">
                <h3 class="text-xs font-bold uppercase tracking-wide text-cyan-200/90">Inviter par e-mail</h3>
                <p class="mt-1 text-xs text-zinc-500">Compte inexistant : lien d’inscription ou de connexion (14 jours). Compte existant : utilisez le menu ci-dessous.</p>
                <form method="POST" action="{{ route('admin.projects.invite-email', $project) }}" class="mt-4 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
                    @csrf
                    <div class="w-full flex-1 sm:min-w-[14rem]">
                        <label for="invite-email" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">E-mail</label>
                        <input id="invite-email" type="email" name="invite_email" value="{{ old('invite_email') }}" required autocomplete="email"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                        @error('invite_email')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex items-center gap-2 text-xs text-zinc-400">
                        <input type="hidden" name="invite_can_view" value="0">
                        <input type="checkbox" name="invite_can_view" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500" @checked((string) old('invite_can_view', '1') === '1')>
                        Voir
                    </label>
                    <label class="flex items-center gap-2 text-xs text-zinc-400">
                        <input type="hidden" name="invite_can_modify" value="0">
                        <input type="checkbox" name="invite_can_modify" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500" @checked((string) old('invite_can_modify') === '1')>
                        Modifier
                    </label>
                    <label class="flex items-center gap-2 text-xs text-zinc-400">
                        <input type="hidden" name="invite_can_annotate" value="0">
                        <input type="checkbox" name="invite_can_annotate" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500" @checked((string) old('invite_can_annotate') === '1')>
                        Annoter
                    </label>
                    <label class="flex items-center gap-2 text-xs text-zinc-400">
                        <input type="hidden" name="invite_can_download" value="0">
                        <input type="checkbox" name="invite_can_download" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500" @checked((string) old('invite_can_download') === '1')>
                        Télécharger
                    </label>
                    <button type="submit" class="rounded-lg border border-cyan-500/40 bg-cyan-600/20 px-4 py-2 text-sm font-semibold text-cyan-200 transition hover:bg-cyan-600/30">Envoyer l’invitation</button>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.projects.members.attach', $project) }}" class="flex flex-col gap-4 border-b border-zinc-800 pb-6 sm:flex-row sm:flex-wrap sm:items-end">
                @csrf
                <div class="w-full flex-1 sm:min-w-[12rem]">
                    <label for="m-user" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Ajouter un membre</label>
                    <select id="m-user" name="user_id" required
                        class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                        <option value="">— Choisir —</option>
                        @foreach ($allUsers as $u)
                            @continue($project->members->contains('id', $u->id))
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-xs text-zinc-400">
                    <input type="hidden" name="can_view" value="0">
                    <input type="checkbox" name="can_view" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500" checked>
                    Voir
                </label>
                <label class="flex items-center gap-2 text-xs text-zinc-400">
                    <input type="hidden" name="can_modify" value="0">
                    <input type="checkbox" name="can_modify" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500">
                    Modifier
                </label>
                <label class="flex items-center gap-2 text-xs text-zinc-400">
                    <input type="hidden" name="can_annotate" value="0">
                    <input type="checkbox" name="can_annotate" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500">
                    Annoter
                </label>
                <label class="flex items-center gap-2 text-xs text-zinc-400">
                    <input type="hidden" name="can_download" value="0">
                    <input type="checkbox" name="can_download" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500">
                    Télécharger
                </label>
                <button type="submit" class="rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2 text-sm font-semibold text-zinc-200 hover:bg-zinc-700">Ajouter</button>
            </form>

            <div class="space-y-4">
                @forelse ($project->members as $member)
                    <div class="rounded-xl border border-zinc-800/80 bg-zinc-950/40 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-zinc-100">{{ $member->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $member->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.projects.members.detach', [$project, $member]) }}" onsubmit="return confirm('Retirer ce membre du projet ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-300">Retirer</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('admin.projects.members.update', [$project, $member]) }}" class="mt-4 flex flex-wrap items-center gap-4">
                            @csrf
                            @method('PUT')
                            @foreach (['can_view' => 'Voir', 'can_modify' => 'Modifier', 'can_annotate' => 'Annoter', 'can_download' => 'Télécharger'] as $field => $label)
                                <label class="flex items-center gap-2 text-xs text-zinc-400">
                                    <input type="hidden" name="{{ $field }}" value="0">
                                    <input type="checkbox" name="{{ $field }}" value="1" class="rounded border-zinc-600 bg-zinc-900 text-indigo-500" @checked($member->pivot->{$field})>
                                    {{ $label }}
                                </label>
                            @endforeach
                            <button type="submit" class="rounded-md border border-zinc-600 px-3 py-1 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Mettre à jour les droits</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">Aucun membre invité pour l’instant.</p>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
