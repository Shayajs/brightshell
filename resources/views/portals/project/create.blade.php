@extends('layouts.admin')

@section('title', 'Nouveau projet')
@section('topbar_label', 'Portail projets')

@push('topbar_extra')
    <a href="{{ route('portals.project') }}"
        class="flex items-center gap-2 rounded-lg border border-zinc-600 bg-zinc-800/80 px-3 py-2 text-xs font-semibold text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-800">
        Annuler
    </a>
@endpush

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project') }}" class="text-cyan-400/90 hover:text-cyan-300">← Tableau de bord</a>
    </p>

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Nouveau projet</h1>
        <p class="mt-1 max-w-2xl text-sm text-zinc-500">
            Après création, vous serez redirigé vers l’administration pour inviter des membres et définir les droits (voir, modifier, annoter, télécharger).
        </p>
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

    <form method="POST" action="{{ route('portals.project.store') }}" class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        @csrf

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="p-name" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Nom</label>
                <input id="p-name" type="text" name="name" value="{{ old('name') }}" required
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-cyan-500/50 focus:outline-none focus:ring-1 focus:ring-cyan-500/30">
            </div>
            <div>
                <label for="p-slug" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Slug URL (optionnel)</label>
                <input id="p-slug" type="text" name="slug" value="{{ old('slug') }}"
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white focus:border-cyan-500/50 focus:outline-none focus:ring-1 focus:ring-cyan-500/30"
                    placeholder="Généré depuis le nom si vide">
            </div>
            <div>
                <label for="p-company" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Société (optionnel)</label>
                <select id="p-company" name="company_id"
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-cyan-500/50 focus:outline-none focus:ring-1 focus:ring-cyan-500/30">
                    <option value="">—</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" @selected((int) old('company_id', 0) === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="p-desc" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Description</label>
                <textarea id="p-desc" name="description" rows="4"
                    class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-cyan-500/50 focus:outline-none focus:ring-1 focus:ring-cyan-500/30">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg border border-cyan-500/40 bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-500">Créer le projet</button>
            <a href="{{ route('portals.project') }}" class="rounded-lg border border-zinc-600 px-4 py-2 text-sm font-semibold text-zinc-300 hover:bg-zinc-800">Annuler</a>
        </div>
    </form>
</div>
@endsection
