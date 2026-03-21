@extends('layouts.admin')

@section('title', 'Dossier — '.$folder->name)
@section('topbar_label', 'Dossier')

@section('content')
<div class="mx-auto max-w-lg space-y-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.student-subjects.show', [$user, $subject, 'dossier' => $folder->id]) }}"
           class="flex size-9 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="font-display text-xl font-bold text-white">Modifier le dossier</h1>
            <p class="text-sm text-zinc-500">Matière : {{ $subject->title }}</p>
        </div>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.student-subject-folders.update', [$user, $subject, $folder->id]) }}" class="space-y-5">
        @csrf @method('PUT')

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-zinc-300">Nom</label>
            <input type="text" id="name" name="name" required value="{{ old('name', $folder->name) }}"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
        </div>

        <div>
            <label for="parent_id" class="mb-1.5 block text-sm font-medium text-zinc-300">Emplacement</label>
            <select id="parent_id" name="parent_id"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                <option value="">Racine de la matière</option>
                @foreach ($options as $opt)
                    <option value="{{ $opt->id }}" @selected(old('parent_id', $folder->parent_id) == $opt->id)>{{ $opt->name }} (id {{ $opt->id }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="sort_order" class="mb-1.5 block text-sm font-medium text-zinc-300">Ordre</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="32767" required
                   value="{{ old('sort_order', $folder->sort_order) }}"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
        </div>

        <div class="flex justify-end border-t border-zinc-800 pt-6">
            <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
