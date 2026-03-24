@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Cahier des charges — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Cahier des charges</h1>
        <p class="mt-1 text-sm text-zinc-500">Les brouillons ne sont visibles qu’avec le droit <strong class="text-zinc-400">modifier</strong>.</p>
    </header>

    @can('update', $project)
        <form method="POST" action="{{ route('portals.project.specs.store', $project) }}" class="space-y-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            @csrf
            <h2 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Nouvelle section</h2>
            <input type="text" name="title" required placeholder="Titre" value="{{ old('title') }}" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            <textarea name="body" rows="6" placeholder="Contenu Markdown" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white">{{ old('body') }}</textarea>
            <select name="status" class="rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                <option value="{{ \App\Models\ProjectSpecSection::STATUS_DRAFT }}">Brouillon</option>
                <option value="{{ \App\Models\ProjectSpecSection::STATUS_PUBLISHED }}">Publié</option>
            </select>
            <button type="submit" class="rounded-lg bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Ajouter</button>
        </form>
    @endcan

    <div class="space-y-6">
        @forelse ($sections as $section)
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 ring-1 ring-white/5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h2 class="font-display text-lg font-bold text-white">{{ $section->title }}</h2>
                    <span class="rounded-md border border-zinc-600 px-2 py-0.5 text-[10px] font-semibold uppercase text-zinc-400">
                        {{ $section->status === \App\Models\ProjectSpecSection::STATUS_PUBLISHED ? 'Publié' : 'Brouillon' }}
                    </span>
                </div>
                <div class="prose prose-invert prose-sm mt-4 max-w-none prose-headings:text-zinc-100 prose-p:text-zinc-300 prose-a:text-cyan-400">
                    {!! Str::markdown($section->body ?? '') !!}
                </div>
                @can('update', $project)
                    <details class="mt-4 border-t border-zinc-800 pt-4">
                        <summary class="cursor-pointer text-xs font-semibold text-cyan-400">Éditer / supprimer</summary>
                        <form method="POST" action="{{ route('portals.project.specs.update', [$project, $section]) }}" class="mt-3 space-y-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="title" value="{{ $section->title }}" required class="w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1 text-sm text-white">
                            <textarea name="body" rows="5" class="w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1 font-mono text-sm text-white">{{ $section->body }}</textarea>
                            <select name="status" class="rounded border border-zinc-700 bg-zinc-950 px-2 py-1 text-sm text-white">
                                <option value="{{ \App\Models\ProjectSpecSection::STATUS_DRAFT }}" @selected($section->status === \App\Models\ProjectSpecSection::STATUS_DRAFT)>Brouillon</option>
                                <option value="{{ \App\Models\ProjectSpecSection::STATUS_PUBLISHED }}" @selected($section->status === \App\Models\ProjectSpecSection::STATUS_PUBLISHED)>Publié</option>
                            </select>
                            <button type="submit" class="text-sm font-semibold text-cyan-400">Enregistrer</button>
                        </form>
                        <form method="POST" action="{{ route('portals.project.specs.destroy', [$project, $section]) }}" class="mt-2" onsubmit="return confirm('Supprimer cette section ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-400">Supprimer la section</button>
                        </form>
                    </details>
                @endcan
            </article>
        @empty
            <p class="text-center text-sm text-zinc-500">Aucune section.</p>
        @endforelse
    </div>
</div>
@endsection
