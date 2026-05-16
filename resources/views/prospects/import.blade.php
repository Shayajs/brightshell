@extends('layouts.admin')

@section('title', 'Importateur API')
@section('topbar_label', 'Importateur')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Importateur API</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-zinc-400">
                Lance un import depuis l'API publique « Recherche Entreprises » (data.gouv.fr).
                Chaque entreprise est enrichie (BODACC + BAN + INPI optionnel) puis scorée
                avant insertion en base. Un fichier CSV est généré dans
                <code class="rounded bg-zinc-950 px-1.5 py-0.5 text-xs text-zinc-300">storage/app/prospects/</code>.
            </p>
        </div>

        @livewire('prospects.import-runner')
    </div>
@endsection
