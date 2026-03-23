@extends('layouts.admin')

@section('title', 'Réglages — API')
@section('topbar_label', 'API')

@section('content')
    <div class="w-full min-w-0 space-y-10">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Développeur</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Clés d’API</h1>
            <p class="text-sm text-zinc-400">
                Utilisez un jeton Bearer sur le sous-domaine API (ex. <code class="rounded bg-zinc-800 px-1 text-xs">Authorization: Bearer …</code>).
                Les requêtes nécessitent le rôle développeur et un e-mail vérifié.
            </p>
        </header>

        @include('layouts.partials.flash')

        @if (session('new_api_token_plain'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                <p class="font-semibold">Copiez ce jeton maintenant — il ne sera plus affiché.</p>
                <p class="mt-2 break-all font-mono text-xs text-emerald-200/90">{{ session('new_api_token_plain') }}</p>
            </div>
        @endif

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Nouveau jeton</h2>
            <form method="POST" action="{{ route('portals.settings.api.tokens.store') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-[12rem] flex-1">
                    <label for="api_token_name" class="mb-1.5 block text-sm font-medium text-zinc-300">Nom (ex. machine, projet)</label>
                    <input type="text" id="api_token_name" name="name" value="{{ old('name') }}" required maxlength="255"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                           placeholder="Mon intégration">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Créer un jeton
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Jetons actifs</h2>
            </div>
            <ul class="divide-y divide-zinc-800/80">
                @forelse ($tokens as $t)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-zinc-200">{{ $t->name }}</p>
                            <p class="mt-1 text-xs text-zinc-500">
                                Créé {{ $t->created_at?->timezone(config('app.timezone'))->translatedFormat('j M Y, H:i') }}
                                @if ($t->last_used_at)
                                    · Dernière utilisation {{ $t->last_used_at->timezone(config('app.timezone'))->translatedFormat('j M Y, H:i') }}
                                @else
                                    · Jamais utilisé
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('portals.settings.api.tokens.destroy', $t->id) }}" onsubmit="return confirm('Révoquer ce jeton ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-500/40 px-3 py-1.5 text-xs font-semibold text-red-300 hover:bg-red-500/10">
                                Révoquer
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-zinc-500">Aucun jeton pour l’instant.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
