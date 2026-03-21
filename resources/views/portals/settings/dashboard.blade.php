@extends('layouts.admin')

@section('title', 'Réglages — Tableau de bord')
@section('topbar_label', 'Réglages')

@section('content')
    @include('portals.settings.partials.subnav', ['current' => 'dashboard'])

    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Vue d’ensemble</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Tableau de bord</h1>
            <p class="max-w-2xl text-sm leading-relaxed text-zinc-400">
                Résumé de ton compte, connexions et alertes récentes.
            </p>
        </header>

        @include('layouts.partials.flash')

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Compte</p>
                <p class="mt-2 truncate font-display text-lg font-bold text-white">{{ $user->name }}</p>
                <p class="mt-1 truncate text-xs text-zinc-500">{{ $user->email }}</p>
            </article>

            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Dernière connexion</p>
                @if ($user->previous_login_at)
                    <p class="mt-2 font-display text-lg font-bold text-white">{{ $user->previous_login_at->timezone(config('app.timezone'))->translatedFormat('d MMM Y, H:i') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">IP : {{ $user->previous_login_ip ?? '—' }}</p>
                @else
                    <p class="mt-2 text-sm text-zinc-500">Première session enregistrée — les prochaines connexions afficheront l’historique ici.</p>
                @endif
            </article>

            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Session actuelle</p>
                <p class="mt-2 font-display text-lg font-bold text-emerald-400/90">Active</p>
                <p class="mt-1 text-xs text-zinc-500">IP : {{ request()->ip() }}</p>
            </article>

            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Notifications</p>
                <p class="mt-2 font-display text-2xl font-bold text-white">{{ $unreadNotificationsCount }}</p>
                <p class="mt-1 text-xs text-zinc-500">Non lues (base)</p>
                <a href="{{ route('portals.settings.notifications.edit') }}" class="mt-2 inline-block text-xs font-semibold text-indigo-400 hover:text-indigo-300">Gérer →</a>
            </article>
        </div>

        @if (config('session.driver') === 'database' && $otherSessionsCount > 0)
            <div class="rounded-xl border border-amber-500/25 bg-amber-500/5 px-4 py-3 text-sm text-amber-200/90">
                <strong class="font-semibold">{{ $otherSessionsCount }}</strong> autre(s) session(s) ouverte(s) ailleurs.
                <a href="{{ route('portals.settings.security.edit') }}" class="ml-2 font-semibold text-amber-300 underline hover:no-underline">Voir la sécurité</a>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Dernières notifications</h2>
                @if ($unreadNotificationsCount > 0)
                    <form method="POST" action="{{ route('portals.settings.notifications.read-all') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Tout marquer comme lu</button>
                    </form>
                @endif
            </div>
            <ul class="divide-y divide-zinc-800/80">
                @forelse ($recentNotifications as $n)
                    @php
                        $data = $n->data;
                        $title = is_array($data) ? ($data['title'] ?? class_basename($n->type)) : class_basename($n->type);
                        $body = is_array($data) ? ($data['body'] ?? $data['message'] ?? '') : '';
                    @endphp
                    <li class="flex flex-wrap items-start gap-3 px-5 py-4 text-sm">
                        <span class="mt-0.5 size-2 shrink-0 rounded-full {{ $n->read_at ? 'bg-zinc-600' : 'bg-indigo-500' }}" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-zinc-200">{{ $title }}</p>
                            @if ($body !== '')
                                <p class="mt-0.5 text-xs text-zinc-500">{{ \Illuminate\Support\Str::limit($body, 180) }}</p>
                            @endif
                            <p class="mt-1 text-[10px] text-zinc-600">{{ $n->created_at?->timezone(config('app.timezone'))->translatedFormat('d MMM Y, H:i') }}</p>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-zinc-500">
                        Aucune notification enregistrée pour l’instant. Elles apparaîtront ici (alertes système, rappels, etc.).
                    </li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 ring-1 ring-white/5">
            <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Raccourcis</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('portals.settings.profile.edit') }}" class="rounded-lg border border-zinc-700 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Modifier le profil</a>
                <a href="{{ route('portals.settings.notifications.edit') }}" class="rounded-lg border border-zinc-700 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Notifications navigateur</a>
                <a href="{{ route('portals.settings.security.edit') }}" class="rounded-lg border border-zinc-700 px-3 py-2 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">Mot de passe &amp; sessions</a>
            </div>
        </section>
    </div>
@endsection
