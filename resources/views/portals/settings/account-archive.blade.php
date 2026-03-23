@extends('layouts.admin')

@section('title', 'Archiver le compte')
@section('topbar_label', 'Compte')

@section('content')
    <div class="w-full min-w-0 space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Compte</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Archiver mon compte</h1>
            <p class="text-sm text-zinc-400">
                Votre compte sera <strong class="text-zinc-300">désactivé</strong> (connexion impossible). Les données restent
                en base pour l’administration jusqu’à une suppression définitive ; l’adresse e-mail
                est libérée pour que vous puissiez <strong class="text-zinc-300">vous inscrire à nouveau</strong> avec la même adresse (pratique pour les tests).
            </p>
        </header>

        @include('layouts.partials.flash')

        <form method="POST" action="{{ route('portals.settings.account.destroy') }}" class="space-y-6 rounded-2xl border border-red-900/40 bg-red-950/20 p-6 ring-1 ring-red-500/10">
            @csrf
            @method('DELETE')

            <div>
                <label for="current_password" class="mb-1.5 block text-sm font-medium text-zinc-300">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-red-500/50 focus:outline-none focus:ring-2 focus:ring-red-500/20">
                @error('current_password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-800 bg-zinc-950/50 px-4 py-3">
                <input type="checkbox" name="confirm_archive" value="1" class="mt-1 h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-red-500">
                <span class="text-sm text-zinc-400">Je comprends que mon compte sera archivé et que je devrai me reconnecter avec un nouveau compte si je change d’avis (sauf restauration par un admin).</span>
            </label>
            @error('confirm_archive')<p class="text-xs text-red-400">{{ $message }}</p>@enderror

            <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-800 pt-6">
                <a href="{{ route('portals.settings.security.edit') }}" class="rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-medium text-zinc-400 transition hover:text-zinc-200">
                    Annuler
                </a>
                <button type="submit" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-500">
                    Archiver mon compte
                </button>
            </div>
        </form>
    </div>
@endsection
