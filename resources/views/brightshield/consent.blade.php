@extends('layouts.auth')

@section('title', 'Autoriser l’application')
@section('auth_panel_class', 'auth-panel--wide')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">BrightShield</p>
            <h1 class="mt-2 font-display text-2xl font-bold text-white">Autoriser {{ $clientLabel['title'] ?? $client->name }}</h1>
            <p class="mt-2 text-sm text-zinc-400">
                {{ $clientLabel['description'] ?? 'Cette application demande l’accès à votre compte BrightShell.' }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-700/80 bg-zinc-900/60 p-4 text-sm text-zinc-300">
            <p class="font-medium text-zinc-100">Connecté en tant que</p>
            <p class="mt-1">{{ $user->email }}</p>
        </div>

        <div>
            <p class="text-sm font-medium text-zinc-200">Autorisations demandées</p>
            <ul class="mt-3 space-y-2 text-sm text-zinc-400">
                @foreach ($scopes as $scope)
                    <li class="flex items-start gap-2">
                        <span class="mt-1 inline-block h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                        <span>{{ $scope->description }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        @if (! empty($sharedData))
            <div class="rounded-xl border border-indigo-500/25 bg-indigo-500/5 p-4">
                <p class="text-sm font-medium text-indigo-200">Informations qui seront partagées</p>
                <dl class="mt-3 space-y-1.5 text-sm">
                    @foreach ($sharedData as $scopeKey => $entries)
                        @foreach ($entries as $entry)
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-zinc-400">{{ $entry['label'] }}</dt>
                                <dd class="text-right font-medium text-zinc-200">{{ $entry['value'] }}</dd>
                            </div>
                        @endforeach
                    @endforeach
                </dl>
                <p class="mt-3 text-xs text-zinc-500">
                    Seules ces informations seront transmises à {{ $clientLabel['title'] ?? $client->name }}. Vous pourrez révoquer cet accès à tout moment depuis vos réglages BrightShell.
                </p>
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
            <form method="POST" action="{{ route('passport.authorizations.deny') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-semibold text-zinc-300 hover:bg-zinc-800/80">
                    Refuser
                </button>
            </form>
            <form method="POST" action="{{ route('passport.authorizations.approve') }}">
                @csrf
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Autoriser
                </button>
            </form>
        </div>
    </div>
@endsection
