@extends('layouts.admin')

@section('title', 'Configuration scoring')
@section('topbar_label', 'Configuration')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Configuration du scoring</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-zinc-400">
                Vue lecture seule de la configuration en vigueur (fichier
                <code class="rounded bg-zinc-950 px-1.5 py-0.5 text-xs text-zinc-300">config/prospects.php</code>).
                Ajustez les seuils, les préfixes NAF et les multiplicateurs en éditant ce fichier
                puis lancez <code class="rounded bg-zinc-950 px-1.5 py-0.5 text-xs text-zinc-300">php artisan config:clear</code>.
            </p>
        </div>

        @livewire('prospects.config')
    </div>
@endsection
