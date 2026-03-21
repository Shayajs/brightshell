@extends('layouts.admin')
@section('title', $quiz->title)
@section('topbar_label', 'Quiz')

@section('content')
<div class="mx-auto max-w-3xl space-y-10">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.student-course-quizzes.index', [$user, $studentCourse]) }}"
           class="flex size-9 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="font-display text-2xl font-bold text-white">{{ $quiz->title }}</h1>
            <p class="text-sm text-zinc-500">{{ $studentCourse->title }} — {{ $user->name }}</p>
        </div>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.student-course-quizzes.update', [$user, $studentCourse, $quiz]) }}" class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        @csrf @method('PUT')
        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Métadonnées</h2>
        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-zinc-300">Titre</label>
            <input type="text" id="title" name="title" required value="{{ old('title', $quiz->title) }}"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100">
        </div>
        <div>
            <label for="instructions" class="mb-1.5 block text-sm font-medium text-zinc-300">Consignes</label>
            <textarea id="instructions" name="instructions" rows="2"
                      class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100">{{ old('instructions', $quiz->instructions) }}</textarea>
        </div>
        <div>
            <label for="sort_order" class="mb-1.5 block text-sm font-medium text-zinc-300">Ordre</label>
            <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $quiz->sort_order) }}"
                   class="w-full max-w-xs rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100">
        </div>
        <label class="flex items-center gap-2 text-sm text-zinc-300">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" class="rounded border-zinc-600 bg-zinc-800 text-indigo-500" @checked(old('is_published', $quiz->is_published))>
            Publié
        </label>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Enregistrer</button>
    </form>

    <form method="POST" action="{{ route('admin.student-course-quizzes.destroy', [$user, $studentCourse, $quiz]) }}" onsubmit="return confirm('Supprimer ce quiz ?')" class="flex">
        @csrf @method('DELETE')
        <button type="submit" class="rounded-lg border border-red-500/40 px-4 py-2 text-sm font-semibold text-red-400 hover:bg-red-500/10">Supprimer le quiz</button>
    </form>

    <section class="space-y-4 rounded-2xl border border-violet-500/30 bg-violet-500/5 p-6 ring-1 ring-violet-500/10">
        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-violet-200">Remplacer les questions (JSON / IA)</h2>
        <p class="text-xs text-zinc-500">Écrase toutes les questions existantes.</p>
        <form method="POST" action="{{ route('admin.student-course-quizzes.import-json', [$user, $studentCourse, $quiz]) }}" class="space-y-3">
            @csrf
            <textarea name="import_json" rows="8" required placeholder='{"title":"…","questions":[…]}'
                      class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-200"></textarea>
            @error('import_json')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            <button type="submit" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Importer</button>
        </form>
    </section>

    <section class="space-y-4">
        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Questions</h2>
        @forelse ($quiz->questions as $q)
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-sm font-medium text-zinc-200">{{ $q->body }}</p>
                    <form method="POST" action="{{ route('admin.student-course-quiz-questions.destroy', [$user, $studentCourse, $quiz, $q]) }}" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-300">Supprimer</button>
                    </form>
                </div>
                <ul class="mt-3 space-y-1 text-xs text-zinc-500">
                    @foreach ($q->answers as $a)
                        <li @class(['text-emerald-400/90' => $a->is_correct])>{{ $a->is_correct ? '✓ ' : '· ' }}{{ $a->body }}</li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-sm text-zinc-500">Aucune question — importe un JSON ou ajoute-en une ci-dessous.</p>
        @endforelse
    </section>

    <section class="rounded-2xl border border-zinc-800 bg-zinc-950/40 p-6">
        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Ajouter une question (manuel)</h2>
        <p class="mt-1 text-xs text-zinc-500">2 à 8 réponses ; coche l’index de la bonne réponse (0 = première ligne).</p>
        <form method="POST" action="{{ route('admin.student-course-quiz-questions.store', [$user, $studentCourse, $quiz]) }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-400">Question</label>
                <textarea name="body" rows="2" required class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            @for ($i = 0; $i < 4; $i++)
                <div>
                    <label class="mb-1 block text-xs font-medium text-zinc-400">Réponse {{ $i + 1 }}</label>
                    <input type="text" name="answers[]" value="{{ old('answers.'.$i) }}"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100">
                </div>
            @endfor
            @error('answers')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-400">Index bonne réponse (0–3)</label>
                <input type="number" name="correct_index" min="0" max="3" value="{{ old('correct_index', 0) }}"
                       class="w-24 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100">
                @error('correct_index')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="rounded-lg bg-zinc-700 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-600">Ajouter la question</button>
        </form>
    </section>
</div>
@endsection
