@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    <a
        href="{{ url('/') }}"
        class="mb-6 inline-flex text-sm font-medium text-zinc-500 transition hover:text-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
    >
        ← Retour au site
    </a>

    <h1 class="font-display text-xl font-bold uppercase tracking-[0.12em] text-white sm:text-2xl">Connexion</h1>
    <p class="mt-2 text-sm leading-relaxed text-zinc-400">Accède à ton espace BrightShell.</p>

    <form method="post" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">E-mail</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
            >
            @error('email')
                <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Mot de passe</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
            >
            @error('password')
                <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-400">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
            Se souvenir de moi
        </label>

        <button
            type="submit"
            class="w-full rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-3 text-center text-sm font-semibold uppercase tracking-wider text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-400"
        >
            Se connecter
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 border-t border-zinc-800 pt-6 text-center text-sm text-zinc-500">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="font-medium text-indigo-400 underline-offset-2 hover:text-indigo-300 hover:underline">Créer un compte</a>
        </p>
    @endif
@endsection
