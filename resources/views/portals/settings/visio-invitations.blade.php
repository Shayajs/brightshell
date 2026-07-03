@extends('layouts.admin')

@section('title', 'Réglages — Invitations visio')
@section('topbar_label', 'Invitations visio')

@section('content')
<div class="space-y-8">
    <header class="space-y-2">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-fuchsia-300/90">Visioconférence</p>
        <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Historique invitations</h1>
        <p class="text-sm text-zinc-400">Retrouve les invitations visio reçues sur ton e-mail ou envoyées depuis ton compte.</p>
    </header>

    @include('layouts.partials.flash')

    <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <ul class="divide-y divide-zinc-800/80">
            @forelse ($invitations as $invitation)
                <li class="px-5 py-4 text-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-zinc-200">{{ $invitation->room->title }}</p>
                            <p class="mt-1 text-xs text-zinc-500">
                                Projet: {{ $invitation->room->project?->name ?? '—' }}
                                · Invité: {{ $invitation->email ?: 'lien invité sans e-mail' }}
                            </p>
                            <p class="mt-1 text-xs text-fuchsia-300">
                                <a href="{{ route('visio.join.show', $invitation->token) }}" target="_blank" rel="noopener">{{ route('visio.join.show', $invitation->token) }}</a>
                            </p>
                        </div>
                        <div class="text-right text-xs">
                            @if ($invitation->accepted_at)
                                <p class="font-semibold text-emerald-300">Acceptée</p>
                            @elseif($invitation->isExpired())
                                <p class="font-semibold text-red-300">Expirée</p>
                            @else
                                <p class="font-semibold text-fuchsia-300">Active</p>
                            @endif
                            <p class="mt-1 text-zinc-500">{{ $invitation->created_at?->translatedFormat('j M Y, H:i') }}</p>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-5 py-12 text-center text-sm text-zinc-500">Aucune invitation visio pour l’instant.</li>
            @endforelse
        </ul>
    </section>

    {{ $invitations->links() }}
</div>
@endsection
