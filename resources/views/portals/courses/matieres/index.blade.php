@extends('layouts.admin')

@section('title', 'Matières & fichiers')
@section('topbar_label', 'Matières')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-violet-400/90">Ressources</p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Matières &amp; fichiers</h1>
                <p class="mt-2 max-w-xl text-sm text-zinc-400">
                    Vos matières et dossiers sont créés par l’équipe : ouvrez une matière pour voir les fichiers et les télécharger.
                </p>
            </div>
            <a href="{{ route('portals.courses') }}"
               class="inline-flex items-center justify-center rounded-xl border border-zinc-700 bg-zinc-900/60 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-zinc-600 hover:bg-zinc-800">
                ← Tableau de bord cours
            </a>
        </div>

        @include('layouts.partials.flash')

        @if ($subjects->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/40 p-10 text-center">
                <p class="text-sm text-zinc-400">Aucune matière pour le moment.</p>
                <p class="mt-2 text-xs text-zinc-600">L’équipe peut en ajouter depuis l’administration (Matières par élève).</p>
            </div>
        @else
            <ul class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($subjects as $sub)
                    <li>
                        <a href="{{ route('portals.courses.matieres.show', $sub) }}"
                           class="block rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-violet-500/40 hover:bg-zinc-900/80">
                            <p class="font-display text-lg font-semibold text-white">{{ $sub->title }}</p>
                            @if ($sub->description)
                                <p class="mt-2 line-clamp-2 text-sm text-zinc-500">{{ $sub->description }}</p>
                            @endif
                            <p class="mt-4 text-[11px] font-semibold uppercase tracking-wide text-violet-400/90">
                                {{ $sub->folders_count }} {{ $sub->folders_count === 1 ? 'dossier' : 'dossiers' }} · ouvrir →
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
