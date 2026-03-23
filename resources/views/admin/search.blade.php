@extends('layouts.admin')

@section('title', 'Recherche')
@section('topbar_label', 'Recherche')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Recherche</h1>
            <p class="text-sm text-zinc-500">
                Membres, sociétés et tickets — jusqu’à 20 résultats par catégorie.
            </p>
        </header>

        <form method="GET" action="{{ route('admin.search') }}" class="max-w-xl" role="search">
            <label for="search-page-q" class="sr-only">Terme de recherche</label>
            <div class="flex h-10 items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900/80 px-3 ring-1 ring-transparent focus-within:border-indigo-500/50 focus-within:ring-indigo-500/20">
                <svg class="h-4 w-4 shrink-0 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input
                    id="search-page-q"
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Nom, e-mail, n° de membre, SIRET, sujet de ticket…"
                    autocomplete="off"
                    class="min-w-0 flex-1 border-0 bg-transparent text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:ring-0"
                >
                <button type="submit" class="shrink-0 rounded-md border border-indigo-500/40 bg-indigo-600/90 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-500">
                    Chercher
                </button>
            </div>
        </form>

        @if ($q === '')
            <p class="text-sm text-zinc-500">Saisissez un terme dans la barre en haut ou ci-dessus.</p>
        @else
            @php
                $total = $members->count() + $companies->count() + $tickets->count();
            @endphp
            @if ($total === 0)
                <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 px-4 py-6 text-center text-sm text-zinc-500 ring-1 ring-white/5">
                    Aucun résultat pour « <span class="font-medium text-zinc-300">{{ $q }}</span> ».
                </div>
            @else
                <p class="text-sm text-zinc-400">
                    <span class="font-semibold text-zinc-200">{{ $total }}</span> résultat(s) pour « <span class="font-medium text-zinc-200">{{ $q }}</span> »
                </p>

                @if ($members->isNotEmpty())
                    <section class="space-y-3">
                        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-zinc-400">Membres</h2>
                        <ul class="divide-y divide-zinc-800/80 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
                            @foreach ($members as $member)
                                <li>
                                    <a href="{{ route('admin.members.show', $member) }}" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm transition hover:bg-zinc-800/40">
                                        <span class="font-medium text-zinc-100">{{ $member->name }}</span>
                                        <span class="font-mono text-xs text-zinc-500">{{ $member->email }}</span>
                                        @if ($member->trashed())
                                            <span class="w-full text-[11px] text-amber-400/90 sm:w-auto">Archivé</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($companies->isNotEmpty())
                    <section class="space-y-3">
                        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-zinc-400">Sociétés</h2>
                        <ul class="divide-y divide-zinc-800/80 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
                            @foreach ($companies as $company)
                                <li>
                                    <a href="{{ route('admin.companies.show', $company) }}" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm transition hover:bg-zinc-800/40">
                                        <span class="font-medium text-zinc-100">{{ $company->name }}</span>
                                        @if ($company->siret)
                                            <span class="font-mono text-xs text-zinc-500">{{ $company->siret }}</span>
                                        @endif
                                        @if ($company->trashed())
                                            <span class="w-full text-[11px] text-amber-400/90 sm:w-auto">Archivée</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($tickets->isNotEmpty())
                    <section class="space-y-3">
                        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-zinc-400">Tickets &amp; demandes</h2>
                        <ul class="divide-y divide-zinc-800/80 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
                            @foreach ($tickets as $ticket)
                                <li>
                                    <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="block px-4 py-3 text-sm transition hover:bg-zinc-800/40">
                                        <span class="font-medium text-zinc-100">{{ \Illuminate\Support\Str::limit($ticket->subject, 120) }}</span>
                                        <span class="mt-1 block text-xs text-zinc-500">{{ $ticket->email }} · {{ $ticket->status }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endif
        @endif
    </div>
@endsection
