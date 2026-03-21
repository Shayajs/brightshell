<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @stack('head')
    
    <link rel="icon" href="{{ asset('img/etoile_sans_fond_contours_fin.png') }}" type="image/png">
    
    @vite(['resources/css/app.css'])
    @stack('styles')
    @stack('vite')
    
    @stack('schema')
</head>
<body>
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

    <!-- Top Navigation -->
    <nav>
        <div class="nav-logo">
            <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">BRIGHTSHELL</a>
        </div>
        @if(isset($showNavLinks) && $showNavLinks)
        <ul class="nav-links">
            <li><a href="{{ route('services') }}">Services</a></li>
        </ul>
        @endif
    </nav>

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
