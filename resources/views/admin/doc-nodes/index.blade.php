@extends('layouts.admin')

@section('title', 'Admin — Documentation')
@section('topbar_label', 'Documentation')

@section('content')
    <div class="space-y-8">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Contenu</p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Documentation</h1>
                <p class="text-sm text-zinc-400">Arborescence, rôles lecteurs (héritage si aucun rôle coché sur un nœud).</p>
            </div>
            <a href="{{ route('admin.doc-nodes.create') }}"
               class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                Nouvelle page / dossier
            </a>
        </header>

        @include('layouts.partials.flash')

        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <table class="min-w-full divide-y divide-zinc-800 text-left text-sm">
                <thead class="bg-zinc-900/80 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">Titre</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Parent</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                    @forelse ($nodes as $node)
                        <tr>
                            <td class="px-4 py-3 font-medium text-white">{{ $node->title }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-zinc-500">{{ $node->slug }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $node->parent?->title ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $node->is_folder ? 'Dossier' : 'Page' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.doc-nodes.edit', $node) }}" class="text-indigo-400 hover:text-indigo-300">Modifier</a>
                                <span class="text-zinc-600">·</span>
                                <a href="{{ route('admin.doc-nodes.create', ['parent' => $node->id]) }}" class="text-zinc-400 hover:text-zinc-300">Enfant</a>
                                <form method="POST" action="{{ route('admin.doc-nodes.destroy', $node) }}" class="inline" onsubmit="return confirm('Supprimer cet élément et ses descendants ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-2 text-red-400 hover:text-red-300">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-zinc-500">Aucun contenu. Créez une page ou un dossier racine.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
