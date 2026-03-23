@extends('layouts.admin')

@section('title', 'Accueil')
@section('topbar_label', 'Votre espace')

@section('content')
    <div class="space-y-12">
        <header class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-400/90 sm:text-sm">Portail</p>
            <h1 class="font-display text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">Bonjour {{ $user->greetingFirstName() ?: $user->name }}</h1>
            <p class="max-w-2xl text-base leading-relaxed text-zinc-400 sm:text-lg">
                Choisissez une plateforme ci-dessous ou via le menu en haut à droite. Les raccourcis affichés correspondent à vos accès.
            </p>
        </header>

        <div class="grid gap-5 sm:grid-cols-2 sm:gap-6 xl:grid-cols-3">
            @foreach ($portalTiles as $key => $portal)
                @continue($key === 'home')
                <a
                    href="{{ $portal['href'] }}"
                    class="group flex flex-row items-center gap-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 transition hover:border-indigo-500/40 hover:bg-zinc-900/80 hover:ring-indigo-500/10 sm:gap-6 sm:p-7"
                >
                    @include('layouts.partials.portal-icon-mark', ['key' => $key, 'frame' => 'xl'])
                    <div class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold uppercase tracking-wide text-zinc-500 group-hover:text-indigo-400/90 sm:text-base">{{ $portal['label'] }}</span>
                        <span class="mt-2 inline-flex items-center gap-2 font-display text-xl font-bold text-white sm:text-2xl">
                            Ouvrir
                            <svg class="h-5 w-5 shrink-0 opacity-50 transition group-hover:translate-x-0.5 group-hover:opacity-100 sm:h-6 sm:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <p class="text-center text-sm text-zinc-600 sm:text-base">
            <a href="{{ \App\Support\BrightshellDomain::publicSiteUrl() }}" class="text-zinc-500 underline decoration-zinc-700 underline-offset-4 transition hover:text-zinc-300">← Site public BrightShell</a>
        </p>
    </div>
@endsection
