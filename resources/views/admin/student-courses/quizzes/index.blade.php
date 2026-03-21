@extends('layouts.admin')
@section('title', 'Quiz — '.$studentCourse->title)
@section('topbar_label', 'Cours élève')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.student-courses.student', $user) }}"
               class="mt-1 flex size-9 shrink-0 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">{{ $studentCourse->title }}</p>
                <h1 class="font-display text-2xl font-bold text-white">Quiz</h1>
                <p class="mt-1 text-sm text-zinc-500">Élève : {{ $user->name }} — import JSON possible (sortie IA).</p>
            </div>
        </div>
        <a href="{{ route('admin.student-course-quizzes.create', [$user, $studentCourse]) }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            Nouveau quiz
        </a>
    </div>

    @include('layouts.partials.flash')

    @if ($quizzes->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/40 p-10 text-center text-sm text-zinc-400">
            Aucun quiz. Crée-en un ou importe un JSON à la création.
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($quizzes as $q)
                <li class="flex flex-col gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-display font-semibold text-white">{{ $q->title }}</p>
                        <p class="mt-1 text-xs text-zinc-500">{{ $q->questions_count }} question(s) · {{ $q->is_published ? 'Publié' : 'Brouillon' }}</p>
                    </div>
                    <a href="{{ route('admin.student-course-quizzes.edit', [$user, $studentCourse, $q]) }}"
                       class="shrink-0 rounded-lg border border-zinc-600 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Éditer</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
