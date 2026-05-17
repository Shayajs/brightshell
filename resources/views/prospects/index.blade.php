@extends('layouts.admin')

@section('title', 'Liste des prospects')
@section('topbar_label', 'Prospects')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Liste des prospects</h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-zinc-400">
                    Cliquez sur une ligne pour ouvrir le détail (scoring, finances, BODACC, carte).
                </p>
            </div>
            <a href="{{ route('prospects.import') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvel import
            </a>
        </div>

        <x-livewire-mount name="prospects.dashboard" />

        <x-livewire-mount name="prospects.index" />
    </div>

    <x-livewire-mount name="prospects.slide-over" />
@endsection
