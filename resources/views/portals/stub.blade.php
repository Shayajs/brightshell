@extends('layouts.admin')

@section('title', $title)
@section('topbar_label', $title)

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Portail</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $title }}</h1>
            <p class="max-w-none text-sm leading-relaxed text-zinc-400">Cette zone sera branchée sur les routes et données métier.</p>
        </header>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <h2 class="border-b border-zinc-800 px-5 py-4 font-display text-sm font-bold uppercase tracking-wide text-white">En construction</h2>
            <div class="p-5 text-sm leading-relaxed text-zinc-400">
                <p>Portail en construction — compléter les contrôleurs et vues associés.</p>
            </div>
        </section>
    </div>
@endsection
