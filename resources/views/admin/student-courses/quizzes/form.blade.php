@extends('layouts.admin')
@section('title', 'Nouveau quiz')
@section('topbar_label', 'Quiz')

@section('content')
<div class="mx-auto max-w-2xl space-y-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.student-course-quizzes.index', [$user, $studentCourse]) }}"
           class="flex size-9 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="font-display text-2xl font-bold text-white">Nouveau quiz</h1>
            <p class="text-sm text-zinc-500">{{ $studentCourse->title }} — {{ $user->name }}</p>
        </div>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.student-course-quizzes.store', [$user, $studentCourse]) }}" class="space-y-6">
        @csrf
        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-zinc-300">Titre <span class="text-red-400">*</span></label>
            <input type="text" id="title" name="title" required value="{{ old('title') }}"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            @error('title')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="instructions" class="mb-1.5 block text-sm font-medium text-zinc-300">Consignes (optionnel)</label>
            <textarea id="instructions" name="instructions" rows="2"
                      class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100">{{ old('instructions') }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-zinc-300">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" class="rounded border-zinc-600 bg-zinc-800 text-indigo-500" @checked(old('is_published', true))>
            Publié (visible sur le portail élève)
        </label>

        <div class="rounded-xl border border-dashed border-violet-500/40 bg-violet-500/5 p-4">
            <label for="import_json" class="mb-1.5 block text-sm font-medium text-violet-200">Importer du JSON (optionnel — ex. sortie IA)</label>
            <p class="mb-2 text-xs text-zinc-500">Format : <code class="text-zinc-400">questions[]</code> avec <code class="text-zinc-400">question</code> et <code class="text-zinc-400">answers[]</code> (<code class="text-zinc-400">text</code>, <code class="text-zinc-400">correct</code> bool). Une seule bonne réponse par question.</p>
            <textarea id="import_json" name="import_json" rows="10" placeholder='{"questions":[{"question":"...","answers":[{"text":"A","correct":false},{"text":"B","correct":true}]}]}'
                      class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-200">{{ old('import_json') }}</textarea>
            @error('import_json')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div class="flex justify-end gap-3 border-t border-zinc-800 pt-6">
            <a href="{{ route('admin.student-course-quizzes.index', [$user, $studentCourse]) }}" class="rounded-lg px-4 py-2.5 text-sm text-zinc-500 hover:text-zinc-300">Annuler</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Créer</button>
        </div>
    </form>
</div>
@endsection
