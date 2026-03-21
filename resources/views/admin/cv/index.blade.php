@extends('layouts.admin')
@section('title', 'Mon CV')
@section('topbar_label', 'Mon CV')

@push('topbar_extra')
    <a href="{{ route('cv') }}" target="_blank"
        class="flex items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-xs font-semibold text-zinc-300 transition hover:border-zinc-600 hover:text-white">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        CV public
    </a>
@endpush

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-white">Mon CV</h1>
        <p class="mt-1 text-sm text-zinc-500">Modifie chaque section — les changements sont répercutés immédiatement sur le CV public.</p>
    </div>

    @include('layouts.partials.flash')

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

        @php
        $sections = [
            [
                'href'    => route('admin.cv.contact'),
                'title'   => 'Identité & Contact',
                'desc'    => 'Prénom, nom, titre, e-mail, téléphone, réseaux, résumé profil.',
                'count'   => null,
                'icon'    => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                'color'   => 'indigo',
            ],
            [
                'href'    => route('admin.cv.experience'),
                'title'   => 'Expériences',
                'desc'    => 'Postes, entreprises, dates, descriptions, réalisations.',
                'count'   => count($data['experience'] ?? []),
                'icon'    => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
                'color'   => 'violet',
            ],
            [
                'href'    => route('admin.cv.diplomes'),
                'title'   => 'Diplômes',
                'desc'    => 'Formations, établissements, dates, détails.',
                'count'   => count($data['diplomes'] ?? []),
                'icon'    => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
                'color'   => 'blue',
            ],
            [
                'href'    => route('admin.cv.competences'),
                'title'   => 'Compétences',
                'desc'    => 'Langages, frameworks, bases de données, outils, langues.',
                'count'   => collect($data['competences'] ?? [])->flatten(1)->count(),
                'icon'    => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
                'color'   => 'emerald',
            ],
            [
                'href'    => route('admin.cv.certifications'),
                'title'   => 'Certifications',
                'desc'    => 'Certifications, bénévolat, activités.',
                'count'   => count($data['certifications'] ?? []),
                'icon'    => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>',
                'color'   => 'amber',
            ],
        ];
        @endphp

        @foreach ($sections as $s)
        <a
            href="{{ $s['href'] }}"
            class="group flex flex-col gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 transition hover:border-zinc-600 hover:shadow-lg hover:shadow-black/20"
        >
            <div class="flex items-start justify-between gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-{{ $s['color'] }}-500/15 text-{{ $s['color'] }}-400 ring-1 ring-{{ $s['color'] }}-500/25" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{!! $s['icon'] !!}</svg>
                </span>
                @if ($s['count'] !== null)
                    <span class="rounded-full border border-zinc-700 bg-zinc-800 px-2.5 py-1 text-[11px] font-semibold text-zinc-400">{{ $s['count'] }}</span>
                @endif
            </div>
            <div>
                <p class="font-display text-sm font-bold text-white group-hover:text-{{ $s['color'] }}-300 transition">{{ $s['title'] }}</p>
                <p class="mt-1 text-xs leading-relaxed text-zinc-500">{{ $s['desc'] }}</p>
            </div>
            <div class="flex items-center gap-1 text-xs font-semibold text-zinc-500 group-hover:text-indigo-400 transition">
                Modifier
                <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Aperçu rapide contact --}}
    @php $c = $data['contact'] ?? []; @endphp
    @if (!empty($c['etat_civil']))
    <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/30 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white font-display">
                {{ strtoupper(substr($c['etat_civil']['prenom'] ?? 'L', 0, 1)) }}
            </div>
            <div>
                <p class="font-display text-sm font-bold text-white">{{ ($c['etat_civil']['prenom'] ?? '').' '.($c['etat_civil']['nom'] ?? '') }}</p>
                <p class="text-xs text-zinc-500">{{ $c['etat_civil']['titre'] ?? '' }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-3 text-xs text-zinc-500 sm:ml-4">
            <span>{{ $c['etat_civil']['email'] ?? '' }}</span>
            <span>{{ $c['etat_civil']['telephone'] ?? '' }}</span>
            <span>{{ $c['etat_civil']['localisation'] ?? '' }}</span>
        </div>
        <a href="{{ route('admin.cv.contact') }}" class="ml-auto text-xs font-semibold text-indigo-400 hover:text-indigo-300">Modifier →</a>
    </div>
    @endif
</div>
@endsection
