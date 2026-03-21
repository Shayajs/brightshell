@extends('layouts.admin')

@php $pageTitle = 'Matières par élève'; @endphp

@section('title', $pageTitle)
@section('topbar_label', $pageTitle)

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Ressources</p>
        <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Matières &amp; dossiers</h1>
        <p class="mt-2 max-w-xl text-sm text-zinc-400">Matières au titre libre, dossiers imbriqués et fichiers — tout est <strong class="text-zinc-300">propre à chaque élève</strong>.</p>
    </div>

    @include('layouts.partials.flash')

    @if ($students->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/40 p-10 text-center text-sm text-zinc-400">
            Aucun élève. Attribuez le rôle « Élève » depuis <a href="{{ route('admin.members.index') }}" class="text-indigo-400 hover:underline">Membres</a>.
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/60 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Élève</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3 text-center">Matières</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @foreach ($students as $student)
                        <tr class="hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5 font-medium text-zinc-100">{{ $student->name }}</td>
                            <td class="px-5 py-3.5 text-zinc-500">{{ $student->email }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex min-w-[2rem] justify-center rounded-md border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-xs font-semibold text-zinc-300">{{ $student->student_subjects_count }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.student-subjects.student', $student) }}"
                                   class="inline-flex rounded-lg border border-violet-500/40 bg-violet-500/10 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/20">Gérer les matières</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex justify-center">{{ $students->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
