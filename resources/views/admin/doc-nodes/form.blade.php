@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Admin — Nouvelle doc' : 'Admin — Modifier doc')
@section('topbar_label', 'Documentation')

@section('content')
    <div class="mx-auto max-w-3xl space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Documentation</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                {{ $mode === 'create' ? 'Nouvelle page ou dossier' : 'Modifier' }}
            </h1>
            @if ($parent)
                <p class="text-sm text-zinc-400">Parent : <strong class="text-zinc-200">{{ $parent->title }}</strong></p>
            @endif
        </header>

        @include('layouts.partials.flash')

        <form method="POST"
              action="{{ $mode === 'create' ? route('admin.doc-nodes.store') : route('admin.doc-nodes.update', $node) }}"
              class="space-y-8 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <input type="hidden" name="parent_id" value="{{ old('parent_id', $node->parent_id) }}">

            <div>
                <label for="slug" class="mb-1.5 block text-sm font-medium text-zinc-300">Slug URL (minuscules, tirets)</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $node->slug) }}" required
                       pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 font-mono text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                       placeholder="ma-page">
                @error('slug')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-zinc-300">Titre</label>
                <input type="text" id="title" name="title" value="{{ old('title', $node->title) }}" required maxlength="255"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @error('title')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_folder" value="0">
                <input type="checkbox" id="is_folder" name="is_folder" value="1" class="size-4 rounded border-zinc-600 bg-zinc-800 text-indigo-600"
                       @checked(old('is_folder', $node->is_folder))>
                <label for="is_folder" class="text-sm text-zinc-300">Dossier (pas de corps Markdown, liste les enfants)</label>
            </div>

            <div>
                <label for="sort_order" class="mb-1.5 block text-sm font-medium text-zinc-300">Ordre d’affichage</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $node->sort_order) }}" required min="0"
                       class="w-32 rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @error('sort_order')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="body" class="mb-1.5 block text-sm font-medium text-zinc-300">Corps (Markdown)</label>
                <p class="mb-2 text-xs text-zinc-500">Ignoré pour les dossiers.</p>
                <textarea id="body" name="body" rows="18"
                          class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 font-mono text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">{{ old('body', $node->body) }}</textarea>
                @error('body')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <section class="rounded-xl border border-zinc-800 bg-zinc-950/40 p-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Lecteurs (rôles)</h2>
                <p class="mt-1 text-xs text-zinc-500">Si aucun rôle n’est coché, les règles du dossier parent s’appliquent. À la racine, au moins un rôle doit être défini sur le dossier (ou héritage impossible).</p>
                <ul class="mt-4 space-y-2">
                    @foreach ($roles as $role)
                        <li class="flex items-center gap-3">
                            <input type="checkbox" name="reader_role_ids[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                   class="size-4 rounded border-zinc-600 bg-zinc-800 text-indigo-600"
                                   @checked(in_array($role->id, old('reader_role_ids', $node->exists ? $node->explicitReaderRoles->pluck('id')->all() : []), true))>
                            <label for="role_{{ $role->id }}" class="text-sm text-zinc-300">{{ $role->label }} <span class="text-zinc-600">({{ $role->slug }})</span></label>
                        </li>
                    @endforeach
                </ul>
                @error('reader_role_ids')<p class="mt-2 text-xs text-red-400">{{ $message }}</p>@enderror
            </section>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('admin.doc-nodes.index') }}" class="rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-medium text-zinc-300 hover:bg-zinc-800">Annuler</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
@endsection
