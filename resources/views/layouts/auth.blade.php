<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Compte') — BrightShell</title>
    <link rel="icon" href="{{ asset('img/etoile_sans_fond_contours_fin.png') }}" type="image/png">
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
    @vite(['resources/css/app.css'])
</head>
<body class="auth-body min-h-full bg-zinc-950 text-zinc-100 antialiased">
    <div class="pointer-events-none fixed inset-0 z-0 bg-[radial-gradient(ellipse_90%_60%_at_50%_-10%,rgba(99,102,241,0.14),transparent)]" aria-hidden="true"></div>
    <div class="relative z-10 flex min-h-full items-center justify-center px-5 py-10 sm:px-6">
        <div class="w-full max-w-[26rem] rounded-2xl border border-zinc-800/90 bg-zinc-900/70 p-8 shadow-[0_24px_80px_rgba(0,0,0,0.45)] ring-1 ring-white/5 backdrop-blur-xl sm:p-9">
            @yield('content')
        </div>
    </div>
</body>
</html>
