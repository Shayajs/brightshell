@extends('layouts.admin')

@section('title', $quiz->title)
@section('topbar_label', 'Quiz')

@section('content')
    <div class="mx-auto max-w-2xl space-y-8">
        <div>
            <a href="{{ route('portals.courses') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Mes cours</a>
            <h1 class="mt-3 font-display text-2xl font-bold text-white">{{ $quiz->title }}</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $studentCourse->title }}</p>
        </div>

        @if (session('quiz_result') !== null)
            <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-4 text-center ring-1 ring-emerald-500/20">
                <p class="text-sm font-medium text-emerald-200">Résultat</p>
                <p class="mt-2 font-display text-4xl font-bold text-emerald-400">{{ session('quiz_result') }}%</p>
                <p class="mt-2 text-xs text-zinc-500">Tentative enregistrée. Vous pouvez refaire le quiz pour vous entraîner.</p>
            </div>
        @endif

        @if ($lastAttempt && session('quiz_result') === null)
            <div class="rounded-xl border border-zinc-700 bg-zinc-900/50 px-4 py-3 text-sm text-zinc-400">
                Dernière tentative : <strong class="text-zinc-200">{{ $lastAttempt->score_percent }}%</strong>
                le {{ $lastAttempt->completed_at->translatedFormat('d M Y à H:i') }}
            </div>
        @endif

        @include('layouts.partials.flash')

        @if ($quiz->instructions)
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-4 text-sm text-zinc-300">{{ $quiz->instructions }}</div>
        @endif

        @if ($quiz->questions->isEmpty())
            <p class="text-center text-sm text-zinc-500">Ce quiz n’a pas encore de questions.</p>
        @else
            <form method="POST" action="{{ route('portals.courses.quiz.submit', [$studentCourse, $quiz]) }}" class="space-y-8">
                @csrf
                @foreach ($quiz->questions as $q)
                    <fieldset class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                        <legend class="px-1 font-display text-sm font-semibold text-white">{{ $loop->iteration }}. {{ $q->body }}</legend>
                        <div class="mt-4 space-y-2">
                            @foreach ($q->answers->shuffle() as $a)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-800 bg-zinc-950/50 px-3 py-2.5 transition hover:border-indigo-500/40 has-[:checked]:border-indigo-500/50 has-[:checked]:bg-indigo-500/10">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $a->id }}" required class="mt-1 border-zinc-600 bg-zinc-900 text-indigo-500">
                                    <span class="text-sm text-zinc-300">{{ $a->body }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('answers.'.$q->id)
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </fieldset>
                @endforeach
                <button type="submit" class="w-full rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-900/30 hover:bg-indigo-500">
                    Valider
                </button>
            </form>
        @endif
    </div>
@endsection
