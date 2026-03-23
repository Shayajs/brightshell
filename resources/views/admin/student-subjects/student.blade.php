@extends('layouts.admin')

@php $pageTitle = 'Matières — '.$user->name; @endphp

@section('title', $pageTitle)
@section('topbar_label', 'Matières élève')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.student-subjects.index') }}"
               class="mt-1 flex size-9 shrink-0 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-violet-400/90">{{ $user->email }}</p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white">Matières de {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-zinc-500">Titres libres, dossiers et fichiers visibles par l’élève sur <strong class="text-zinc-400">/matieres</strong>.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.student-courses.student', $user) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Cours</a>
            <a href="{{ route('admin.student-subjects.create', $user) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle matière
            </a>
        </div>
    </div>

    @include('layouts.partials.flash')

    @if ($subjects->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/40 p-10 text-center">
            <p class="text-sm text-zinc-400">Aucune matière. Créez la première avec un titre au choix (ex. « PHP », « Anglais », « Projet X »).</p>
            <a href="{{ route('admin.student-subjects.create', $user) }}" class="mt-3 inline-block text-sm font-medium text-violet-400 hover:underline">Nouvelle matière</a>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($subjects as $sub)
                <li class="flex flex-col gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 ring-1 ring-white/5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-display text-base font-semibold text-white">{{ $sub->title }}</h2>
                        @if ($sub->description)
                            <p class="mt-1 text-sm text-zinc-400 line-clamp-2">{{ $sub->description }}</p>
                        @endif
                        <p class="mt-2 text-xs text-zinc-600">{{ $sub->folders_count }} dossier(s) (tous niveaux)</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <a href="{{ route('admin.student-subjects.show', [$user, $sub]) }}"
                           class="rounded-lg bg-zinc-800 px-3 py-2 text-xs font-semibold text-zinc-200 hover:bg-zinc-700">Ouvrir</a>
                        <a href="{{ route('admin.student-subjects.edit', [$user, $sub]) }}"
                           class="rounded-lg border border-zinc-600 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Modifier</a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
