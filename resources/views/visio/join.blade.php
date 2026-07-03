@extends('layouts.admin')

@section('title', 'Rejoindre la visio')
@section('topbar_label', 'Visio')

@section('content')
<div class="mx-auto max-w-2xl space-y-6 py-8">
    @include('layouts.partials.flash')

    <header class="space-y-2 text-center">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-fuchsia-300/90">Visio BrightShell</p>
        <h1 class="font-display text-3xl font-bold text-white">{{ $room->title }}</h1>
        @if ($room->project)
            <p class="text-sm text-zinc-400">Projet: {{ $room->project->name }}</p>
        @endif
    </header>

    <section class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-6 ring-1 ring-white/5">
        <p class="text-sm text-zinc-400">Aucun compte requis. Rejoignez en un clic avec votre nom d’affichage.</p>

        <form method="POST" action="{{ route('visio.join.submit', $invitation->token) }}" class="mt-5 space-y-4">
            @csrf
            @guest
                <div>
                    <label class="text-xs text-zinc-500">Votre nom</label>
                    <input type="text" name="guest_name" required maxlength="80" placeholder="Ex: Camille" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                </div>
            @else
                <p class="text-xs text-zinc-500">Connecté en tant que {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
            @endguest

            <button type="submit" class="w-full rounded-lg bg-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-fuchsia-500">
                Rejoindre la visioconférence
            </button>
        </form>
    </section>
</div>
@endsection
