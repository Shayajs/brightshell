@extends('layouts.admin')

@section('title', 'Identité & mails')
@section('topbar_label', 'Identité & mails')
@section('portal_main_max', 'max-w-5xl')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-indigo-300 hover:text-indigo-200">← Tableau de bord</a>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Identité vitrine & e-mails</h1>
            <p class="text-sm text-zinc-400">
                Chemins relatifs au dossier <code class="rounded bg-zinc-800 px-1 py-0.5 text-xs text-zinc-300">public/</code>
                (ex. <code class="text-xs">img/logo.webp</code>). Laisser vide pour utiliser le <span class="text-zinc-300">.env</span> ou les défauts.
            </p>
        </header>

        @include('layouts.partials.flash')

        <form method="post" action="{{ route('admin.site-appearance.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white">Logos & favicon (site public)</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="block text-xs text-zinc-400">Favicon</label>
                        <input type="text" name="favicon_path" value="{{ old('favicon_path', $appearance->favicon_path ?? '') }}"
                               placeholder="{{ $defaultFavicon }}"
                               class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-zinc-100">
                        <p class="text-xs text-zinc-500">Défaut config : <span class="font-mono text-zinc-400">{{ $defaultFavicon }}</span></p>
                        @if(old('favicon_path', $appearance->favicon_path))
                            <img src="{{ asset(old('favicon_path', $appearance->favicon_path)) }}" alt="" class="mt-2 h-10 w-10 rounded border border-zinc-700 object-contain bg-zinc-950 p-1">
                        @else
                            <img src="{{ \App\Support\BrightshellBrand::faviconUrl() }}" alt="" class="mt-2 h-10 w-10 rounded border border-zinc-700 object-contain bg-zinc-950 p-1">
                        @endif
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs text-zinc-400">Logo principal (vitrine, OG, e-mails)</label>
                        <input type="text" name="site_logo_path" value="{{ old('site_logo_path', $appearance->site_logo_path ?? '') }}"
                               placeholder="{{ $defaultSiteLogo }}"
                               class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-zinc-100">
                        <p class="text-xs text-zinc-500">Défaut config : <span class="font-mono text-zinc-400">{{ $defaultSiteLogo }}</span></p>
                        <img src="{{ \App\Support\BrightshellBrand::siteLogoUrl() }}" alt="" class="mt-2 max-h-20 max-w-[200px] rounded border border-zinc-700 object-contain bg-zinc-950 p-2">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white">En-têtes & pieds des e-mails</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs text-zinc-400">Nom de marque (e-mail)</label>
                        <input type="text" name="mail_brand_name" value="{{ old('mail_brand_name', $brandMail['name'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400">Slogan</label>
                        <input type="text" name="mail_brand_tagline" value="{{ old('mail_brand_tagline', $brandMail['tagline'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-zinc-400">Signature (pied)</label>
                        <input type="text" name="mail_footer_signature" value="{{ old('mail_footer_signature', $footerMail['signature'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-zinc-400">Mention légale</label>
                        <input type="text" name="mail_footer_legal" value="{{ old('mail_footer_legal', $footerMail['legal'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                    </div>
                </div>
                <p class="mt-3 text-xs text-zinc-500">Laisser un champ vide pour retomber sur la valeur du fichier <span class="font-mono text-zinc-400">config/brightshell.php</span> (section <span class="font-mono">mail_layout</span>).</p>
            </section>

            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-white">Couleurs des e-mails</h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-zinc-300">
                            <input type="hidden" name="mail_use_custom_theme" value="0">
                            <input type="checkbox" name="mail_use_custom_theme" value="1" class="rounded border-zinc-600 bg-zinc-800 text-indigo-500"
                                   @checked((string) old('mail_use_custom_theme', $mailUseCustomTheme ? '1' : '0') === '1')>
                            Personnaliser les couleurs (sinon uniquement <span class="font-mono text-xs text-zinc-400">config/brightshell.php</span>)
                        </label>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $colorFields = [
                            'mail_primary_color' => ['label' => 'Accent / boutons', 'key' => 'primaryColor'],
                            'mail_background_color' => ['label' => 'Fond extérieur', 'key' => 'backgroundColor'],
                            'mail_card_color' => ['label' => 'Fond carte', 'key' => 'cardColor'],
                            'mail_text_color' => ['label' => 'Texte principal', 'key' => 'textColor'],
                            'mail_muted_text_color' => ['label' => 'Texte secondaire', 'key' => 'mutedTextColor'],
                            'mail_button_text_color' => ['label' => 'Texte bouton', 'key' => 'buttonTextColor'],
                            'mail_divider_color' => ['label' => 'Séparateurs', 'key' => 'dividerColor'],
                        ];
                    @endphp
                    @foreach ($colorFields as $inputName => $meta)
                        <div>
                            <label class="block text-xs text-zinc-400">{{ $meta['label'] }}</label>
                            <div class="mt-1 flex gap-2">
                                <input type="color" id="{{ $inputName }}_pick"
                                       value="{{ old($inputName, $theme[$meta['key']] ?? '#000000') }}"
                                       class="h-10 w-14 cursor-pointer rounded border border-zinc-600 bg-zinc-900"
                                       aria-label="{{ $meta['label'] }}">
                                <input type="text" name="{{ $inputName }}" id="{{ $inputName }}"
                                       value="{{ old($inputName, $theme[$meta['key']] ?? '') }}"
                                       class="min-w-0 flex-1 rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-sm text-zinc-100"
                                       pattern="^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$"
                                       placeholder="#000000">
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-zinc-500">Coche « Personnaliser les couleurs » pour enregistrer ces valeurs en base. Sinon, seules les couleurs du fichier de config s’appliquent.</p>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Enregistrer
                </button>
            </div>
        </form>

        <div class="rounded-xl border border-zinc-800/80 bg-zinc-950/40 p-4">
            <p class="text-xs text-zinc-500">Réinitialiser uniquement les <strong class="text-zinc-400">couleurs</strong> e-mail (pas le texte ni les logos).</p>
            <form method="post" action="{{ route('admin.site-appearance.reset-mail-theme') }}" class="mt-2" onsubmit="return confirm('Réinitialiser les couleurs des e-mails vers les valeurs du fichier de config ?');">
                @csrf
                <button type="submit" class="rounded-lg border border-zinc-600 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:bg-zinc-800">
                    Réinitialiser les couleurs e-mail
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const pairs = [
                ['mail_primary_color'],
                ['mail_background_color'],
                ['mail_card_color'],
                ['mail_text_color'],
                ['mail_muted_text_color'],
                ['mail_button_text_color'],
                ['mail_divider_color'],
            ];
            pairs.forEach(([name]) => {
                const text = document.getElementById(name);
                const pick = document.getElementById(name + '_pick');
                if (!text || !pick) return;
                const syncPick = () => {
                    if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(text.value)) pick.value = text.value.length === 4
                        ? '#' + text.value[1] + text.value[1] + text.value[2] + text.value[2] + text.value[3] + text.value[3]
                        : text.value;
                };
                text.addEventListener('input', syncPick);
                pick.addEventListener('input', () => { text.value = pick.value; });
                syncPick();
            });
        })();
    </script>
@endpush
