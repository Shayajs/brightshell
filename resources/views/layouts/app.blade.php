<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @stack('head')

    {{-- Gilroy : URL réelle (APP_URL, sous-dossier) pour CSS + FontFace dans le JS --}}
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

    @use('App\Support\BrightshellBrand')
    <link rel="icon" href="{{ BrightshellBrand::faviconUrl() }}" type="{{ BrightshellBrand::faviconMimeType() }}">
    
    @vite(['resources/css/app.css'])
    @stack('styles')
    @stack('vite')
    
    @stack('schema')
</head>
<body
    class="brightshell-vitrine {{ trim($bodyClass ?? '') }}"
    data-brightshell-authed="{{ auth()->check() ? '1' : '0' }}"
    data-brightshell-user-name="{{ auth()->check() ? e(auth()->user()->greetingFirstName() ?: auth()->user()->name) : '' }}"
    data-brightshell-login-url="{{ $brightshellLoginUrl }}"
    data-brightshell-space-url="{{ $brightshellSpaceUrl }}"
>
    @if(isset($backgroundMinimal) && $backgroundMinimal)
        <!-- Background minimal -->
        <div class="background-decoration-minimal">
            <div class="circle small" style="top: 10%; left: 15%;"></div>
            <div class="circle medium" style="top: 70%; right: 10%; animation-delay: 3s;"></div>
        </div>
    @else
        <!-- Background decorative elements -->
        <div class="background-decoration">
            <div class="circle small" style="top: 15%; left: 20%;"></div>
            <div class="circle medium" style="top: 60%; left: 10%; animation-delay: 2s;"></div>
            <div class="circle medium" style="top: 20%; right: 15%; animation-delay: 4s;"></div>
            <div class="circle large" style="bottom: 10%; right: 5%; animation-delay: 1s;"></div>

            <div class="star" style="top: 25%; left: 45%; animation-delay: 0.5s;"></div>
            <div class="star" style="top: 70%; left: 75%; animation-delay: 1.5s;"></div>
            <div class="star" style="bottom: 15%; left: 30%; animation-delay: 2.5s;"></div>
            <div class="star" style="top: 40%; right: 20%; animation-delay: 3s;"></div>

            <div class="geometric-line line-1"></div>
            <div class="geometric-line line-2"></div>
        </div>
    @endif

    <!-- Top Navigation (classe dédiée : évite d’écraser .portal-nav dans app.css) -->
    @unless(isset($hideTopNav) && $hideTopNav)
        <nav class="site-top-nav" aria-label="Navigation principale">
            <div class="nav-logo">
                <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">BRIGHTSHELL</a>
                @if(($bodyClass ?? '') === 'home-vitrine')
                    <a href="{{ route('quesako.index') }}" class="home-nav-quesako-arrow" aria-label="Aller vers Quesako" data-transition-link>
                        <span class="home-nav-quesako-arrow__shaft"></span>
                        <span class="home-nav-quesako-arrow__head"></span>
                    </a>
                @endif
            </div>
            @if(isset($showNavLinks) && $showNavLinks)
            <ul class="nav-links">
                @auth
                    <li>
                        <a href="{{ $brightshellSpaceUrl }}">
                            {{ auth()->user()->greetingFirstName() ?: auth()->user()->name }}
                        </a>
                    </li>
                    <li>
                        <form class="nav-logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Déconnexion</button>
                        </form>
                    </li>
                @else
                    <li>
                        <a href="{{ $brightshellLoginUrl }}">Connexion · Inscription</a>
                    </li>
                @endauth
            </ul>
            @endif
        </nav>
    @endunless

    <!-- Main Content -->
    <main @if(isset($mainClass))class="{{ $mainClass }}"@endif>
        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        @yield('bottom-nav')
    </div>

    @if(isset($showScrollIndicator) && $showScrollIndicator)
    <!-- Scroll Indicator -->
    <div class="scroll-indicator"></div>
    @endif

    @stack('scripts')
</body>
</html>
