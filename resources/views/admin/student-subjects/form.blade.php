@extends('layouts.admin')

@php
    $sub = $subject ?? null;
    $isEdit = $sub !== null;
    $pageTitle = $isEdit ? 'Modifier la matière' : 'Nouvelle matière — '.$user->name;
@endphp

@section('title', $pageTitle)
@section('topbar_label', $isEdit ? 'Matière' : 'Nouvelle matière')

@section('content')
<div class="mx-auto max-w-xl space-y-8">
    <div class="flex items-center gap-4">
        <a href="{{ $isEdit ? route('admin.student-subjects.show', [$user, $sub]) : route('admin.student-subjects.student', $user) }}"
           class="flex size-9 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-display text-2xl font-bold text-white">{{ $pageTitle }}</h1>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ $isEdit ? route('admin.student-subjects.update', [$user, $sub]) : route('admin.student-subjects.store', $user) }}" class="space-y-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-zinc-300">Titre de la matière <span class="text-red-400">*</span></label>
            <input type="text" id="title" name="title" required value="{{ old('title', $sub?->title ?? '') }}"
                   placeholder="Ex. Symfony, Maths, Projet site vitrine…"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 placeholder-zinc-600 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
            @error('title')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium text-zinc-300">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">{{ old('description', $sub?->description ?? '') }}</textarea>
        </div>

        @if ($isEdit)
        <div>
            <label for="sort_order" class="mb-1.5 block text-sm font-medium text-zinc-300">Ordre d’affichage</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="32767" required
                   value="{{ old('sort_order', $sub->sort_order) }}"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
        </div>
        @endif

        <div class="flex justify-end border-t border-zinc-800 pt-6">
            <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
        </div>
    </form>

    @if ($isEdit)
        <form method="POST" action="{{ route('admin.student-subjects.destroy', [$user, $sub]) }}" onsubmit="return confirm('Supprimer cette matière et tout son contenu (dossiers + fichiers) ?')" class="border-t border-zinc-800 pt-6">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm font-medium text-red-400 hover:text-red-300">Supprimer définitivement cette matière</button>
        </form>
    @endif
</div>
@endsection
