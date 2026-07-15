@extends('layouts.admin')

@section('title', 'Réglages — Applications connectées')
@section('topbar_label', 'Applications connectées')

@section('content')
    <div class="w-full min-w-0 space-y-10">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">BrightShield</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Applications connectées</h1>
            <p class="text-sm text-zinc-400">
                Sites et applications autorisés à utiliser votre compte BrightShell via BrightShield.
            </p>
        </header>

        @include('layouts.partials.flash')

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Accès actifs</h2>
            </div>
            <ul class="divide-y divide-zinc-800/80">
                @forelse ($apps as $app)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-zinc-200">{{ $app['label']['title'] ?? $app['client']?->name }}</p>
                            @if (! empty($app['label']['description']))
                                <p class="mt-1 text-xs text-zinc-500">{{ $app['label']['description'] }}</p>
                            @endif
                            <p class="mt-1 text-xs text-zinc-500">
                                Autorisé le {{ $app['consent']->granted_at?->timezone(config('app.timezone'))->translatedFormat('j M Y, H:i') }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('portals.settings.connected-apps.destroy', $app['consent']->client_id) }}" onsubmit="return confirm('Révoquer l’accès pour cette application ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-500/40 px-3 py-1.5 text-xs font-semibold text-red-300 hover:bg-red-500/10">
                                Révoquer
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-zinc-500">Aucune application connectée pour l’instant.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
