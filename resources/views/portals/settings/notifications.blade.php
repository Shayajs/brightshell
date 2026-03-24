@extends('layouts.admin')

@section('title', 'Réglages — Notifications')
@section('topbar_label', 'Notifications')

@section('content')
    <div class="space-y-10">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Alertes</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Notifications</h1>
            <p class="max-w-none text-sm text-zinc-400">
                Prépare les <strong class="text-zinc-300">notifications du navigateur</strong> (base pour une future web app / PWA).
                L’historique ci-dessous utilise les notifications enregistrées en base Laravel.
            </p>
        </header>

        @include('layouts.partials.flash')

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Navigateur</h2>
            <p class="mt-2 text-sm text-zinc-500">Demandez l’autorisation une fois depuis ce portail : les autres sous-domaines BrightShell délégueront ensuite l’affichage au bridge notifications.</p>

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
                        <span class="mt-0.5 block text-xs font-normal text-zinc-500">Enregistrez votre préférence côté compte (indépendamment de l’autorisation du navigateur).</span>
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
                                <p class="mt-2 text-[10px] text-zinc-600">{{ $n->created_at?->timezone(config('app.timezone'))->translatedFormat('j M Y, H:i') }}</p>
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
            const bridge = window.BrightshellNotifications;
            if (!bridge || typeof bridge.getPermission !== 'function') {
                if (status) status.textContent = 'Bridge notifications indisponible.';
                if (btn) btn.disabled = true;
                return;
            }

            function label(p) {
                if (p === 'granted') return 'Autorisation accordée.';
                if (p === 'denied') return 'Autorisations refusées — modifie-les dans les réglages du navigateur.';
                return 'Autorisation non demandée ou ignorée.';
            }
            bridge.getPermission().then((permission) => {
                if (status) status.textContent = label(permission);
            });
            btn?.addEventListener('click', async function () {
                try {
                    const res = await bridge.requestPermission();
                    if (status) status.textContent = label(res.permission || 'default');
                } catch (e) {
                    const code = e?.data?.code || e?.message || '';
                    if (code === 'request_permission_requires_settings_origin') {
                        const url = e?.data?.settingsUrl || bridge.bridgeUrl || '';
                        if (status) status.textContent = url
                            ? `Ouvre ${url} pour accorder l’autorisation (demande bloquée hors settings).`
                            : 'Demande bloquée sur ce sous-domaine : ouvre settings pour autoriser.';
                        return;
                    }
                    if (status) status.textContent = `Impossible de demander l’autorisation (${code || 'erreur inconnue'}).`;
                }
            });
        })();
    </script>
@endpush
