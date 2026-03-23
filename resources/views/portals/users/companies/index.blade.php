@extends('layouts.admin')

@section('title', 'Mes sociétés')
@section('topbar_label', 'Mes sociétés')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Entreprise</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Sociétés rattachées</h1>
            <p class="max-w-none text-sm leading-relaxed text-zinc-400">
                Vous voyez ici les sociétés associées à votre compte. Selon les droits définis par l’administration,
                vous pouvez consulter en lecture seule ou modifier la fiche et le logo.
            </p>
        </header>

        @if ($companies->isEmpty())
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8 text-center text-sm text-zinc-500 ring-1 ring-white/5">
                Aucune société liée pour l’instant. Contactez BrightShell si vous pensez que c’est une erreur.
            </div>
        @else
            <ul class="space-y-3">
                @foreach ($companies as $c)
                    @php
                        $canManage = $user->canManageClientCompany($c);
                    @endphp
                    <li>
                        <a
                            href="{{ route('portals.users.companies.show', $c) }}"
                            class="flex flex-wrap items-center gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-4 ring-1 ring-white/5 transition hover:border-zinc-700"
                        >
                            @if ($c->logoUrl())
                                <img src="{{ $c->logoUrl() }}" alt="" class="h-12 w-12 shrink-0 rounded-xl border border-zinc-800 object-contain bg-zinc-950 p-0.5">
                            @else
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-zinc-800 bg-zinc-950 text-sm font-bold text-zinc-600 font-display">
                                    {{ strtoupper(substr($c->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-zinc-100">{{ $c->name }}</p>
                                <p class="text-xs text-zinc-500">
                                    @if ($canManage)
                                        Vous pouvez modifier cette société
                                    @else
                                        Consultation seule
                                    @endif
                                </p>
                            </div>
                            <span class="text-xs font-semibold text-indigo-400">Voir →</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
