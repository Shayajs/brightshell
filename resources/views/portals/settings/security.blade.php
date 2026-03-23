@extends('layouts.admin')

@section('title', 'Réglages — Sécurité')
@section('topbar_label', 'Sécurité')

@section('content')
    <div class="mx-auto max-w-2xl space-y-10">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Protection du compte</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Sécurité</h1>
            <p class="text-sm text-zinc-400">Mot de passe et sessions actives (si les sessions sont stockées en base).</p>
        </header>

        @include('layouts.partials.flash')

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Changer le mot de passe</h2>
            <form method="POST" action="{{ route('portals.settings.security.password') }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-1.5 block text-sm font-medium text-zinc-300">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('current_password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-zinc-300">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-zinc-300">Confirmation</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                        Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
        </section>

        @if (config('session.driver') === 'database')
            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Sessions</h2>
                        <p class="mt-1 text-xs text-zinc-500">Appareils récemment connectés (d’après la table des sessions).</p>
                    </div>
                    @if ($sessions->count() > 1)
                        <form method="POST" action="{{ route('portals.settings.security.sessions.destroy-others') }}" onsubmit="return confirm('Déconnecter toutes les autres sessions ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-500/40 px-3 py-2 text-xs font-semibold text-red-300 hover:bg-red-500/10">
                                Déconnecter les autres
                            </button>
                        </form>
                    @endif
                </div>

                <ul class="mt-6 space-y-3">
                    @forelse ($sessions as $s)
                        @php
                            $isCurrent = $s->id === session()->getId();
                            $when = \Illuminate\Support\Carbon::createFromTimestamp($s->last_activity)->timezone(config('app.timezone'));
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-zinc-800/80 bg-zinc-950/40 px-4 py-3 text-sm">
                            <div>
                                <p class="font-medium text-zinc-200">
                                    {{ $isCurrent ? 'Cette session' : 'Session' }}
                                    @if ($isCurrent)
                                        <span class="ml-2 rounded bg-emerald-500/15 px-1.5 py-0.5 text-[10px] font-bold uppercase text-emerald-400">actuelle</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-zinc-500">{{ $s->ip_address ?? '—' }} · {{ \Illuminate\Support\Str::limit($s->user_agent ?? '', 64) }}</p>
                                <p class="mt-1 text-[10px] text-zinc-600">Dernière activité : {{ $when->translatedFormat('d MMM Y, H:i') }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-lg border border-dashed border-zinc-700 px-4 py-8 text-center text-sm text-zinc-500">Aucune session enregistrée.</li>
                    @endforelse
                </ul>
            </section>
        @else
            <p class="rounded-xl border border-zinc-800 bg-zinc-900/40 px-4 py-3 text-sm text-zinc-500">
                Le pilote de session n’est pas <code class="rounded bg-zinc-800 px-1">database</code> : la liste des appareils n’est pas disponible sur cette instance.
            </p>
        @endif
    </div>
@endsection
