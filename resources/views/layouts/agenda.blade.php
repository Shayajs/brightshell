<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Agenda') — BrightShell</title>
    @use('App\Support\BrightshellBrand')
    <link rel="icon" href="{{ BrightshellBrand::faviconUrl() }}" type="{{ BrightshellBrand::faviconMimeType() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    @stack('head')
</head>
<body class="min-h-full bg-zinc-950 text-zinc-100 antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>
