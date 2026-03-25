<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Compte') — BrightShell</title>
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
</head>
<body class="auth-body auth-shell min-h-full text-zinc-100 antialiased">
    <canvas id="auth-perlin-canvas" class="auth-perlin-canvas" aria-hidden="true"></canvas>
    <div class="auth-shell__overlay" aria-hidden="true"></div>

    <header class="auth-shell__header">
        <a href="{{ config('app.url') }}" class="auth-shell__brand">BRIGHTSHELL</a>
    </header>

    <div class="auth-shell__layout">
        <div class="auth-shell__panel @yield('auth_panel_class')">
            @yield('content')
        </div>
    </div>
</body>
</html>
