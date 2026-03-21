@extends('layouts.admin')

@section('title', 'Cours')
@section('topbar_label', 'Portail cours')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Apprentissage</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Parcours &amp; modules</h1>
            <p class="max-w-2xl text-sm leading-relaxed text-zinc-400">
                Salut <strong class="font-medium text-zinc-200">{{ $user->name }}</strong>. Cours personnels, matières avec dossiers et fichiers partagés par l’équipe.
            </p>
            <p class="pt-2">
                <a href="{{ route('portals.courses.matieres.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-violet-500/40 bg-violet-500/10 px-4 py-2.5 text-sm font-semibold text-violet-200 transition hover:bg-violet-500/20">
                    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Matières &amp; fichiers
                    @if ($subjectsCount > 0)
                        <span class="rounded-md border border-violet-400/30 bg-violet-500/20 px-2 py-0.5 text-[11px] font-bold text-violet-100">{{ $subjectsCount }}</span>
                    @endif
                </a>
            </p>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Mes cours</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">{{ $courses->count() }}</p>
                <p class="mt-1 text-xs text-zinc-500">Parcours personnels, gérés par l’équipe</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">En cours</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">{{ $courses->where('status', 'in_progress')->count() }}</p>
                <p class="mt-1 text-xs text-zinc-500">Statut « En cours »</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-700">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Terminés</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">{{ $courses->where('status', 'completed')->count() }}</p>
                <p class="mt-1 text-xs text-zinc-500">Bravo pour la régularité</p>
            </article>
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-violet-500/30">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Matières</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">{{ $subjectsCount }}</p>
                <p class="mt-1 text-xs text-zinc-500">Dossiers &amp; fichiers</p>
                <a href="{{ route('portals.courses.matieres.index') }}" class="mt-3 inline-block text-xs font-semibold text-violet-400 hover:text-violet-300">Ouvrir →</a>
            </article>
        </div>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <h2 class="border-b border-zinc-800 px-5 py-4 font-display text-sm font-bold uppercase tracking-wide text-white">Mes cours</h2>
            <div class="divide-y divide-zinc-800/80">
                @forelse ($courses as $course)
                    <div class="px-5 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-display text-base font-semibold text-white">{{ $course->title }}</p>
                                @if ($course->description)
                                    <p class="mt-1 text-sm text-zinc-400">{{ $course->description }}</p>
                                @endif
                                <p class="mt-2 text-xs text-zinc-600">
                                    @if ($course->starts_at) Début {{ $course->starts_at->translatedFormat('d M Y') }} @endif
                                    @if ($course->ends_at) · Fin {{ $course->ends_at->translatedFormat('d M Y') }} @endif
                                </p>
                                @if ($course->hasWeeklySchedule())
                                    <p class="mt-1 text-xs text-amber-200/80">
                                        Chaque {{ strtolower($course->scheduleWeekdayLabel() ?? '') }} {{ $course->scheduleTimeStartShort() }}–{{ $course->scheduleTimeEndShort() }}
                                    </p>
                                @endif
                                @if ($course->quizzes->isNotEmpty())
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($course->quizzes as $qz)
                                            <li>
                                                <a href="{{ route('portals.courses.quiz.show', [$course, $qz]) }}"
                                                   class="inline-flex items-center rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-2.5 py-1 text-[11px] font-semibold text-indigo-300 hover:bg-indigo-500/20">
                                                    Quiz : {{ $qz->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            @php
                                $badge = match ($course->status) {
                                    'completed' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
                                    'in_progress' => 'border-indigo-500/30 bg-indigo-500/10 text-indigo-300',
                                    'archived' => 'border-zinc-600 bg-zinc-800 text-zinc-500',
                                    default => 'border-zinc-600 bg-zinc-800/50 text-zinc-400',
                                };
                            @endphp
                            <span class="shrink-0 rounded-md border px-2.5 py-1 text-[11px] font-semibold uppercase {{ $badge }}">{{ $course->statusLabel() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-zinc-500">
                        Aucun cours assigné pour le moment. L’équipe peut en ajouter depuis l’administration (section <strong class="text-zinc-400">Cours élèves</strong>).
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
