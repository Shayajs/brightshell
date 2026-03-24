@extends('layouts.admin')
@section('title', 'Nouveau groupe collaborateur')
@section('topbar_label', 'Groupes collaborateurs')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex flex-wrap items-center gap-3 text-sm">
        <a href="{{ route('admin.collaborator-teams.index') }}" class="text-zinc-500 hover:text-indigo-400">← Groupes</a>
    </div>

    <h1 class="font-display text-2xl font-bold text-white">Nouveau groupe</h1>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.collaborator-teams.store') }}" class="space-y-8 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="name" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nom affiché</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="128"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Slug (optionnel)</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" maxlength="64" placeholder="auto à partir du nom"
                       pattern="[a-z0-9]+(-[a-z0-9]+)*"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                @error('slug')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3">
                <input type="hidden" name="is_admin_team" value="0">
                <input type="checkbox" name="is_admin_team" value="1" @checked(old('is_admin_team'))
                       class="mt-0.5 h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
                <div>
                    <p class="text-sm font-medium text-zinc-100">Équipe d’administration collaborateurs</p>
                    <p class="mt-1 text-xs text-zinc-500">Une seule à la fois : cocher retire ce statut aux autres groupes. Ce groupe peut gérer les permissions des autres équipes dans le portail collaborateurs.</p>
                </div>
            </label>
            @error('is_admin_team')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        @include('admin.collaborator-teams.partials.capability-fields', ['allCapabilities' => $allCapabilities, 'team' => null])

        <div class="flex justify-end gap-3 border-t border-zinc-800 pt-6">
            <a href="{{ route('admin.collaborator-teams.index') }}" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm font-semibold text-zinc-300 hover:border-zinc-500">Annuler</a>
            <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Créer</button>
        </div>
    </form>
</div>
@endsection
