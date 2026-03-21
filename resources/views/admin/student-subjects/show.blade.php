@extends('layouts.admin')

@section('title', $subject->title)
@section('topbar_label', 'Matière')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.student-subjects.student', $user) }}"
               class="mt-1 flex size-9 shrink-0 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-violet-400/90">{{ $user->name }}</p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white">{{ $subject->title }}</h1>
                @if ($subject->description)
                    <p class="mt-2 max-w-2xl text-sm text-zinc-400">{{ $subject->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.student-subjects.edit', [$user, $subject]) }}"
               class="rounded-lg border border-zinc-600 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Modifier la matière</a>
        </div>
    </div>

    @include('layouts.partials.flash')

    {{-- Nouveau dossier racine --}}
    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
        <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">Nouveau dossier à la racine</h2>
        <form method="POST" action="{{ route('admin.student-subject-folders.store', [$user, $subject]) }}" class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <div class="min-w-0 flex-1">
                <label for="root-folder-name" class="sr-only">Nom du dossier</label>
                <input type="text" id="root-folder-name" name="name" required placeholder="Nom du dossier"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-zinc-700 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-600">Créer</button>
        </form>
    </div>

    {{-- Arborescence (gauche) + inspecteur dossier (droite, xl+) --}}
    @if ($folderTree->isEmpty())
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">Dossiers &amp; fichiers</h2>
            <p class="mt-4 text-sm text-zinc-500">Aucun dossier. Crée un dossier racine ci-dessus, puis clique sur un dossier pour gérer fichiers et accès élève (panneau de droite sur grand écran).</p>
        </div>
    @else
        <div class="flex flex-col gap-8 xl:grid xl:grid-cols-2 xl:items-start xl:gap-8">
            <div class="min-w-0 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">Dossiers &amp; fichiers</h2>
                <p class="mt-2 text-[11px] text-zinc-600">Clique sur un dossier pour le sélectionner. Les actions (import, Créer MD, verrouillage) sont dans le panneau de droite sur écran large ; en dessous sur mobile.</p>
                <div class="mt-4 space-y-2">
                    @foreach ($folderTree as $node)
                        @include('admin.student-subjects.partials.folder-node', ['node' => $node, 'user' => $user, 'subject' => $subject, 'depth' => 0, 'activeFolder' => $activeFolder])
                    @endforeach
                </div>
            </div>
            <aside class="min-w-0">
                @include('admin.student-subjects.partials.folder-inspector', ['user' => $user, 'subject' => $subject, 'activeFolder' => $activeFolder])
            </aside>
        </div>
    @endif
</div>
@endsection
