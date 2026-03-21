@extends('layouts.admin')

@section('title', $subject->title)
@section('topbar_label', $subject->title)

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('portals.courses.matieres.index') }}" class="text-sm text-zinc-500 hover:text-violet-400">← Toutes les matières</a>
                <h1 class="mt-3 font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $subject->title }}</h1>
                @if ($subject->description)
                    <p class="mt-2 max-w-2xl text-sm text-zinc-400">{{ $subject->description }}</p>
                @endif
            </div>
        </div>

        @include('layouts.partials.flash')

        @if ($folderTree->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/40 p-8 text-center text-sm text-zinc-500">
                Aucun dossier dans cette matière pour le moment.
            </div>
        @else
            <section class="space-y-4">
                <h2 class="font-display text-xs font-bold uppercase tracking-wider text-zinc-500">Dossiers &amp; fichiers</h2>
                <div class="space-y-4">
                    @foreach ($folderTree as $node)
                        @include('portals.courses.matieres.partials.folder-node', ['node' => $node, 'depth' => 0])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
