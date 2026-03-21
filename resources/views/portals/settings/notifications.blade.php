@extends('layouts.admin')

@section('title', 'Réglages — Notifications')
@section('topbar_label', 'Réglages')

@section('content')
    @include('portals.settings.partials.subnav', ['current' => 'notifications'])

    <div class="space-y-10">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Alertes</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Notifications</h1>
            <p class="max-w-2xl text-sm text-zinc-400">
                Prépare les <strong class="text-zinc-300">notifications du navigateur</strong> (base pour une future web app / PWA).
                L’historique ci-dessous utilise les notifications enregistrées en base Laravel.
            </p>
        </header>

        @include('layouts.partials.flash')

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Navigateur</h2>
            <p class="mt-2 text-sm text-zinc-500">Demande l’autorisation à ton navigateur. Tu pourras affiner les types d’alertes quand la web app sera branchée.</p>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="button" id="browser-notif-request"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Demander l’autorisation
                </button>
                <span id="browser-notif-status" class="text-sm text-zinc-500"></span>
            </div>

            <form method="POST" action="{{ route('portals.settings.notifications.update') }}" class="mt-8 space-y-4 border-t border-zinc-800 pt-8">
                @csrf
                @method('PUT')
                <input type="hidden" name="browser_notifications_enabled" value="0">
                <label class="flex cursor-pointer items-start gap-3 text-sm text-zinc-300">
                    <input type="checkbox" name="browser_notifications_enabled" value="1" @checked(old('browser_notifications_enabled', $user->browser_notifications_enabled))
                           class="mt-1 rounded border-zinc-600 bg-zinc-800 text-indigo-500">
                    <span>
                        <span class="font-medium">J’accepte les notifications navigateur BrightShell</span>
                        <span class="mt-0.5 block text-xs font-normal text-zinc-500">Enregistre ta préférence côté compte (indépendamment de l’autorisation du navigateur).</span>
                    </span>
                </label>
                <button type="submit" class="rounded-lg border border-zinc-600 px-4 py-2 text-xs font-semibold text-zinc-200 hover:bg-zinc-800">
                    Enregistrer la préférence
                </button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Historique (base)</h2>
                @if ($user->unreadNotifications->isNotEmpty())
                    <form method="POST" action="{{ route('portals.settings.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Tout marquer comme lu</button>
                    </form>
                @endif
            </div>
            <ul class="divide-y divide-zinc-800/80">
                @forelse ($notifications as $n)
                    @php
                        $data = $n->data;
                        $title = is_array($data) ? ($data['title'] ?? class_basename($n->type)) : class_basename($n->type);
                        $body = is_array($data) ? ($data['body'] ?? $data['message'] ?? '') : '';
                    @endphp
                    <li class="px-5 py-4 text-sm">
                        <div class="flex items-start gap-3">
                            <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $n->read_at ? 'bg-zinc-600' : 'bg-indigo-500' }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-zinc-200">{{ $title }}</p>
                                @if ($body !== '')
                                    <p class="mt-1 text-xs text-zinc-500">{{ $body }}</p>
                                @endif
                                <p class="mt-2 text-[10px] text-zinc-600">{{ $n->created_at?->timezone(config('app.timezone'))->translatedFormat('d MMM Y, H:i') }}</p>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-12 text-center text-sm text-zinc-500">Aucune entrée pour l’instant.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const btn = document.getElementById('browser-notif-request');
            const status = document.getElementById('browser-notif-status');
            if (!('Notification' in window)) {
                if (status) status.textContent = 'Ce navigateur ne supporte pas les notifications.';
                if (btn) btn.disabled = true;
                return;
            }
            function label(p) {
                if (p === 'granted') return 'Autorisation accordée.';
                if (p === 'denied') return 'Autorisations refusées — modifie-les dans les réglages du navigateur.';
                return 'Autorisation non demandée ou ignorée.';
            }
            if (status) status.textContent = label(Notification.permission);
            btn?.addEventListener('click', async function () {
                try {
                    const p = await Notification.requestPermission();
                    if (status) status.textContent = label(p);
                } catch (e) {
                    if (status) status.textContent = 'Impossible de demander l’autorisation.';
                }
            });
        })();
    </script>
@endpush
