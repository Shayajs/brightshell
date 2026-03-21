@extends('layouts.admin')

@php $pageTitle = 'Cours — '.$user->name; @endphp

@section('title', $pageTitle)
@section('topbar_label', 'Cours élève')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.student-courses.index') }}"
               class="mt-1 flex size-9 shrink-0 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">{{ $user->email }}</p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white">Cours de {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-zinc-500">{{ $courses->count() }} cours — visibles uniquement pour cet élève sur le portail Cours.</p>
            </div>
        </div>
        <a href="{{ route('admin.student-courses.create', $user) }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau cours
        </a>
    </div>

    @include('layouts.partials.flash')

    @if (count($scheduleConflicts) > 0)
        <div class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200 ring-1 ring-amber-500/20" role="alert">
            <p class="font-semibold">Créneaux qui se chevauchent</p>
            <p class="mt-1 text-xs text-amber-200/80">Ajuste les horaires dans chaque cours concerné.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-amber-100/90">
                @foreach ($scheduleConflicts as $cid => $titles)
                    @php $c = $courses->firstWhere('id', $cid); @endphp
                    @if ($c)
                        <li><strong>{{ $c->title }}</strong> ↔ {{ implode(', ', $titles) }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if ($courses->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/40 p-10 text-center">
            <p class="text-sm text-zinc-400">Aucun cours pour cet élève.</p>
            <a href="{{ route('admin.student-courses.create', $user) }}" class="mt-3 inline-block text-sm font-medium text-indigo-400 hover:underline">Créer le premier cours</a>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($courses as $course)
                <li class="flex flex-col gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 ring-1 ring-white/5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-display text-base font-semibold text-white">{{ $course->title }}</h2>
                            @php
                                $badge = match ($course->status) {
                                    'completed' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
                                    'in_progress' => 'border-indigo-500/30 bg-indigo-500/10 text-indigo-300',
                                    'archived' => 'border-zinc-600 bg-zinc-800 text-zinc-500',
                                    default => 'border-zinc-600 bg-zinc-800/50 text-zinc-400',
                                };
                            @endphp
                            <span class="rounded-md border px-2 py-0.5 text-[10px] font-semibold uppercase {{ $badge }}">{{ $course->statusLabel() }}</span>
                            <span class="text-[10px] text-zinc-600">#{{ $course->sort_order }}</span>
                        </div>
                        @if ($course->description)
                            <p class="mt-2 text-sm text-zinc-400 line-clamp-2">{{ $course->description }}</p>
                        @endif
                        <p class="mt-2 text-xs text-zinc-600">
                            @if ($course->starts_at)
                                Début {{ $course->starts_at->translatedFormat('d M Y') }}
                            @endif
                            @if ($course->ends_at)
                                · Fin {{ $course->ends_at->translatedFormat('d M Y') }}
                            @endif
                        </p>
                        @if ($course->hasWeeklySchedule())
                            <p class="mt-1 text-xs font-medium text-amber-200/90">
                                📅 {{ $course->scheduleWeekdayLabel() }} {{ $course->scheduleTimeStartShort() }} – {{ $course->scheduleTimeEndShort() }}
                                @if (isset($scheduleConflicts[$course->id]))
                                    <span class="text-red-400">· chevauchement</span>
                                @endif
                            </p>
                        @endif
                        <p class="mt-1 text-[11px] text-zinc-500">{{ $course->quizzes_count }} quiz</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <a href="{{ route('admin.student-course-quizzes.index', [$user, $course]) }}"
                           class="rounded-lg border border-violet-500/40 bg-violet-500/10 px-3 py-2 text-xs font-semibold text-violet-300 hover:bg-violet-500/20">Quiz</a>
                        <a href="{{ route('admin.student-courses.edit', [$user, $course]) }}"
                           class="rounded-lg border border-zinc-600 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Modifier</a>
                        <form method="POST" action="{{ route('admin.student-courses.destroy', [$user, $course]) }}" onsubmit="return confirm('Supprimer ce cours ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-2 text-xs font-semibold text-red-400 hover:bg-red-500/10">Supprimer</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
