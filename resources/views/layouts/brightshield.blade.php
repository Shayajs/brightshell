<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BrightShield') — BrightShell</title>
    @use('App\Support\BrightshellBrand')
    <link rel="icon" href="{{ BrightshellBrand::faviconUrl() }}" type="{{ BrightshellBrand::faviconMimeType() }}">
    <link rel="preload" href="{{ asset('fonts/Gilroy-ExtraBold.otf') }}" as="font" type="font/otf" crossorigin>
    <script>
        window.__BRIGHTSHELL_FONT_URL = @json(asset('fonts/Gilroy-ExtraBold.otf'));
    </script>
    <style>
        @font-face {
            font-family: 'Gilroy ExtraBold';
            font-style: normal;
            font-weight: 800;
            font-display: block;
            src: url('{{ asset("fonts/Gilroy-ExtraBold.otf") }}') format('opentype');
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/auth-perlin.js'])
    @stack('vite')
</head>
<body class="auth-body auth-shell min-h-full text-zinc-100 antialiased">
    <canvas id="auth-perlin-canvas" class="auth-perlin-canvas" aria-hidden="true"></canvas>
    <div class="auth-shell__overlay" aria-hidden="true"></div>

    <header class="auth-shell__header">
        <a href="{{ config('app.url') }}" class="auth-shell__brand">BRIGHTSHELL</a>
        <span class="auth-shell__brand-sep" aria-hidden="true">·</span>
        <span class="auth-shell__brand-sub">BrightShield</span>
    </header>

    <div class="auth-shell__layout auth-shell__layout--brightshield">
        <aside class="brightshield-brand-stage" aria-label="Connexion entre BrightShell et {{ $clientLabel['title'] ?? $client->name }}">
            <div class="brightshield-logo-pair">
                <figure class="brightshield-logo-pair__item">
                    <img
                        src="{{ BrightshellBrand::siteLogoUrl() }}"
                        alt="BrightShell"
                        class="brightshield-logo-pair__img"
                        width="160"
                        height="160"
                    >
                    <figcaption class="brightshield-logo-pair__caption">BrightShell</figcaption>
                </figure>

                <span class="brightshield-logo-pair__x" aria-hidden="true">×</span>

                <figure class="brightshield-logo-pair__item">
                    @if (! empty($appIconUrl))
                        <img
                            src="{{ $appIconUrl }}"
                            alt="{{ $clientLabel['title'] ?? $client->name }}"
                            class="brightshield-logo-pair__img"
                            width="160"
                            height="160"
                        >
                    @else
                        <span class="brightshield-logo-pair__fallback">
                            {{ strtoupper(mb_substr($clientLabel['title'] ?? $client->name, 0, 1)) }}
                        </span>
                    @endif
                    <figcaption class="brightshield-logo-pair__caption">{{ $clientLabel['title'] ?? $client->name }}</figcaption>
                </figure>
            </div>
            <p class="brightshield-brand-stage__tagline">
                Connectez votre compte BrightShell à {{ $clientLabel['title'] ?? $client->name }}
            </p>
        </aside>

        <div class="auth-shell__panel auth-shell__panel--wide auth-shell__panel--brightshield">
            @yield('content')
        </div>
    </div>
</body>
</html>
