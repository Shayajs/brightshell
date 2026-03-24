@extends('layouts.admin')

@php
    $isEdit = $widget->exists;
    $qpJson = old('query_params_json', json_encode($widget->query_params ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $hdrJson = old('headers_json', json_encode($widget->headers ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $pathsJson = old('display_paths_json', json_encode($widget->display_paths ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
@endphp

@section('title', $isEdit ? 'Modifier le module API' : 'Nouveau module API')
@section('topbar_label', 'API sortantes')

@section('content')
    <div class="space-y-8">
        <p class="text-sm text-zinc-500">
            <a href="{{ route('admin.outbound-api-widgets.index') }}" class="text-indigo-400 hover:text-indigo-300">← Modules API sortantes</a>
        </p>

        <header>
            <h1 class="font-display text-2xl font-bold text-white">{{ $isEdit ? $widget->title : 'Nouveau module' }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                Identifiant interne <code class="rounded bg-zinc-900 px-1 text-xs">{{ $isEdit ? $widget->name : '—' }}</code>
                @unless($isEdit)
                    — renseigné à la création ci-dessous.
                @endunless
            </p>
        </header>

        @include('layouts.partials.flash')

        @if ($errors->any())
            <div class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="outbound-widget-form" method="POST"
            action="{{ $isEdit ? route('admin.outbound-api-widgets.update', $widget) : route('admin.outbound-api-widgets.store') }}"
            class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            @csrf
            @if ($isEdit)
                @method('PUT')
                <input type="hidden" name="name" value="{{ old('name', $widget->name) }}">
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                @unless($isEdit)
                    <div>
                        <label for="w-name" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Identifiant (slug)</label>
                        <input id="w-name" type="text" name="name" value="{{ old('name') }}" required pattern="[a-z0-9_\-]+"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                            placeholder="meteo_paris">
                        <p class="mt-1 text-[11px] text-zinc-600">Lettres minuscules, chiffres, tirets et underscores.</p>
                    </div>
                @endunless
                <div class="{{ $isEdit ? 'sm:col-span-2' : '' }}">
                    <label for="w-title" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Titre affiché</label>
                    <input id="w-title" type="text" name="title" value="{{ old('title', $widget->title) }}" required
                        class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label for="w-enabled" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Actif</label>
                    <label class="mt-2 flex items-center gap-2 text-sm text-zinc-300">
                        <input id="w-enabled" type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $widget->is_enabled))
                            class="rounded border-zinc-600 bg-zinc-950 text-indigo-600 focus:ring-indigo-500/40">
                        Afficher sur le tableau de bord
                    </label>
                </div>
                <div>
                    <label for="w-sort" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Ordre</label>
                    <input id="w-sort" type="number" name="sort_order" value="{{ old('sort_order', $widget->sort_order) }}" min="0"
                        class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                </div>
            </div>

            <div class="border-t border-zinc-800 pt-6">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Requête HTTP</h2>
                <div class="mt-4 grid gap-5 sm:grid-cols-6">
                    <div class="sm:col-span-1">
                        <label for="w-method" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Méthode</label>
                        <select id="w-method" name="http_method"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                            @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'] as $m)
                                <option value="{{ $m }}" @selected(old('http_method', $widget->http_method) === $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-5">
                        <label for="w-url" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">URL</label>
                        <input id="w-url" type="url" name="url" value="{{ old('url', $widget->url) }}" required
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                            placeholder="https://api.example.com/v1/data">
                    </div>
                    <div class="sm:col-span-3">
                        <label for="w-qp" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Query (JSON objet)</label>
                        <textarea id="w-qp" name="query_params_json" rows="4"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-200 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">{{ $qpJson }}</textarea>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="w-hdr" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">En-têtes (JSON objet)</label>
                        <textarea id="w-hdr" name="headers_json" rows="4"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-200 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">{{ $hdrJson }}</textarea>
                    </div>
                    <div class="sm:col-span-6">
                        <label for="w-body" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Corps (POST/PUT/PATCH)</label>
                        <textarea id="w-body" name="body" rows="5"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-200 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">{{ old('body', $widget->body) }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="w-timeout" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Timeout (s)</label>
                        <input id="w-timeout" type="number" name="timeout_seconds" value="{{ old('timeout_seconds', $widget->timeout_seconds) }}" min="3" max="120" required
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-800 pt-6">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Authentification</h2>
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="w-auth" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Type</label>
                        <select id="w-auth" name="auth_type"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                            <option value="{{ \App\Models\AdminOutboundApiWidget::AUTH_NONE }}" @selected(old('auth_type', $widget->auth_type) === \App\Models\AdminOutboundApiWidget::AUTH_NONE)>Aucune</option>
                            <option value="{{ \App\Models\AdminOutboundApiWidget::AUTH_BEARER }}" @selected(old('auth_type', $widget->auth_type) === \App\Models\AdminOutboundApiWidget::AUTH_BEARER)>Bearer</option>
                            <option value="{{ \App\Models\AdminOutboundApiWidget::AUTH_API_KEY_HEADER }}" @selected(old('auth_type', $widget->auth_type) === \App\Models\AdminOutboundApiWidget::AUTH_API_KEY_HEADER)>Clé — en-tête</option>
                            <option value="{{ \App\Models\AdminOutboundApiWidget::AUTH_API_KEY_QUERY }}" @selected(old('auth_type', $widget->auth_type) === \App\Models\AdminOutboundApiWidget::AUTH_API_KEY_QUERY)>Clé — query</option>
                            <option value="{{ \App\Models\AdminOutboundApiWidget::AUTH_BASIC }}" @selected(old('auth_type', $widget->auth_type) === \App\Models\AdminOutboundApiWidget::AUTH_BASIC)>HTTP Basic</option>
                        </select>
                    </div>
                    <div>
                        <label for="w-secret" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Secret / mot de passe</label>
                        <input id="w-secret" type="password" name="auth_secret" autocomplete="off"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                            placeholder="{{ $isEdit ? 'Laisser vide pour ne pas changer' : '' }}">
                    </div>
                    <div>
                        <label for="w-hname" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Nom d’en-tête (clé header)</label>
                        <input id="w-hname" type="text" name="auth_header_name" value="{{ old('auth_header_name', $widget->auth_header_name) }}"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                            placeholder="X-Api-Key">
                    </div>
                    <div>
                        <label for="w-qparam" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Nom paramètre query</label>
                        <input id="w-qparam" type="text" name="auth_query_param" value="{{ old('auth_query_param', $widget->auth_query_param) }}"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                            placeholder="api_key">
                    </div>
                    <div>
                        <label for="w-basic-u" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Utilisateur Basic</label>
                        <input id="w-basic-u" type="text" name="basic_username" value="{{ old('basic_username', $widget->basic_username) }}"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-800 pt-6">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Quand appeler l’API</h2>
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="w-fetch" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Mode</label>
                        <select id="w-fetch" name="fetch_mode"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                            <option value="live" @selected(old('fetch_mode', $widget->fetch_mode) === 'live')>À la demande (à chaque chargement du tableau de bord)</option>
                            <option value="scheduled" @selected(old('fetch_mode', $widget->fetch_mode) === 'scheduled')>Planifié (cache, rafraîchi par la tâche planifiée)</option>
                        </select>
                        <p class="mt-1 text-[11px] text-zinc-600">La commande <code class="text-zinc-500">outbound-api-widgets:sync</code> tourne toutes les 5 minutes.</p>
                    </div>
                    <div>
                        <label for="w-cron" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Intervalle (minutes)</label>
                        <input id="w-cron" type="number" name="cron_interval_minutes" value="{{ old('cron_interval_minutes', $widget->cron_interval_minutes ?? 15) }}" min="1" max="10080"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-800 pt-6">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Affichage sur le tableau de bord</h2>
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="w-dmode" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Mode</label>
                        <select id="w-dmode" name="display_mode"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                            <option value="raw_json" @selected(old('display_mode', $widget->display_mode) === 'raw_json')>JSON brut (indenté si possible)</option>
                            <option value="key_paths" @selected(old('display_mode', $widget->display_mode) === 'key_paths')>Chemins JSON (notation point)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="w-paths" class="block text-xs font-semibold uppercase tracking-wide text-zinc-500">Chemins (JSON tableau de chaînes)</label>
                        <textarea id="w-paths" name="display_paths_json" rows="3"
                            class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-200 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                            placeholder='["main.temp", "weather.0.description"]'>{{ $pathsJson }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-zinc-800 pt-6">
                <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">Enregistrer</button>
            </div>
        </form>

        @if ($isEdit)
            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('admin.outbound-api-widgets.test', $widget) }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-lg border border-zinc-600 bg-zinc-800/80 px-4 py-2 text-sm font-semibold text-zinc-200 transition hover:bg-zinc-700">Tester maintenant</button>
                </form>
                <form method="POST" action="{{ route('admin.outbound-api-widgets.destroy', $widget) }}" class="inline" onsubmit="return confirm('Supprimer ce module ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/20">Supprimer</button>
                </form>
            </div>
        @endif
    </div>
@endsection
