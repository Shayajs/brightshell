@extends('layouts.admin')

@php
    $isEdit = $course !== null;
    $pageTitle = $isEdit ? 'Modifier le cours' : 'Nouveau cours — '.$user->name;
@endphp

@section('title', $pageTitle)
@section('topbar_label', $isEdit ? 'Modifier cours' : 'Nouveau cours')

@section('content')
<div class="mx-auto max-w-2xl space-y-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.student-courses.student', $user) }}"
           class="flex size-9 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="font-display text-2xl font-bold text-white">{{ $pageTitle }}</h1>
            <p class="text-sm text-zinc-500">Élève : <strong class="text-zinc-300">{{ $user->name }}</strong></p>
        </div>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ $isEdit ? route('admin.student-courses.update', [$user, $course]) : route('admin.student-courses.store', $user) }}" class="space-y-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-zinc-300">Titre <span class="text-red-400">*</span></label>
            <input type="text" id="title" name="title" required value="{{ old('title', $course->title ?? '') }}"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            @error('title')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium text-zinc-300">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">{{ old('description', $course->description ?? '') }}</textarea>
        </div>

        <div>
            <label for="status" class="mb-1.5 block text-sm font-medium text-zinc-300">Statut</label>
            <select id="status" name="status"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $course->status ?? 'planned') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if ($isEdit)
        <div>
            <label for="sort_order" class="mb-1.5 block text-sm font-medium text-zinc-300">Ordre d’affichage</label>
            <input type="number" id="sort_order" name="sort_order" min="0" max="32767"
                   value="{{ old('sort_order', $course->sort_order) }}"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="starts_at" class="mb-1.5 block text-sm font-medium text-zinc-300">Date de début</label>
                <input type="date" id="starts_at" name="starts_at"
                       value="{{ old('starts_at', isset($course) && $course->starts_at ? $course->starts_at->format('Y-m-d') : '') }}"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
            <div>
                <label for="ends_at" class="mb-1.5 block text-sm font-medium text-zinc-300">Date de fin</label>
                <input type="date" id="ends_at" name="ends_at"
                       value="{{ old('ends_at', isset($course) && $course->ends_at ? $course->ends_at->format('Y-m-d') : '') }}"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
        </div>

        <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4 ring-1 ring-amber-500/10">
            <p class="text-sm font-medium text-amber-200/90">Emploi du temps (créneau récurrent)</p>
            <p class="mt-1 text-xs text-zinc-500">Optionnel : même jour chaque semaine. Les chevauchements pour cet élève sont bloqués à l’enregistrement.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="schedule_weekday" class="mb-1.5 block text-xs font-medium text-zinc-400">Jour</label>
                    <select id="schedule_weekday" name="schedule_weekday"
                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100">
                        <option value="">—</option>
                        @foreach ([1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi',7=>'Dimanche'] as $n => $lab)
                            <option value="{{ $n }}" @selected((string) old('schedule_weekday', $course?->schedule_weekday ?? '') === (string) $n)>{{ $lab }}</option>
                        @endforeach
                    </select>
                    @error('schedule_weekday')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="schedule_time_start" class="mb-1.5 block text-xs font-medium text-zinc-400">Début</label>
                    <input type="time" id="schedule_time_start" name="schedule_time_start"
                           value="{{ old('schedule_time_start', $course?->scheduleTimeStartShort() ?? '') }}"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100">
                    @error('schedule_time_start')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="schedule_time_end" class="mb-1.5 block text-xs font-medium text-zinc-400">Fin</label>
                    <input type="time" id="schedule_time_end" name="schedule_time_end"
                           value="{{ old('schedule_time_end', $course?->scheduleTimeEndShort() ?? '') }}"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100">
                    @error('schedule_time_end')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div>
            <label for="notes" class="mb-1.5 block text-sm font-medium text-zinc-300">Notes internes (non visibles par l’élève)</label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">{{ old('notes', $course->notes ?? '') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 border-t border-zinc-800 pt-6">
            <a href="{{ route('admin.student-courses.student', $user) }}" class="rounded-lg px-4 py-2.5 text-sm text-zinc-500 hover:text-zinc-300">Annuler</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ $isEdit ? 'Enregistrer' : 'Créer le cours' }}
            </button>
        </div>
    </form>
</div>
@endsection
