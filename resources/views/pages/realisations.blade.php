@push('head')
    <!-- Primary Meta Tags -->
    <title>Portfolio & Réalisations | BrightShell - Projets Web Développés</title>
    <meta name="title" content="Portfolio & Réalisations | BrightShell - Projets Web Développés">
    <meta name="description" content="Découvrez le portfolio et les réalisations de BrightShell : AlloTata (gestion pour micro-entreprises), Takeoff (gestion d'aéroclub), Lotixam (gestion immobilière), et projets personnels. Applications web complètes développées avec PHP, Symfony, JavaScript.">
    <meta name="keywords" content="portfolio développeur, réalisations web, projets développés, AlloTata, Takeoff, Lotixam, applications web, systèmes de gestion, portfolio BrightShell, exemples de développement, projets PHP Symfony, France">
    <meta name="author" content="BrightShell - Lucas ESPINAR">
    <meta name="robots" content="index, follow">
    <meta name="language" content="French">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('realisations') }}">
    <meta property="og:title" content="Portfolio & Réalisations | BrightShell - Projets Web Développés">
    <meta property="og:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="og:image" content="{{ asset('img/logo_sans_fond_contours_epais.webp') }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="BrightShell">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ route('realisations') }}">
    <meta property="twitter:title" content="Portfolio & Réalisations | BrightShell - Projets Web Développés">
    <meta property="twitter:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="twitter:image" content="{{ asset('img/logo_sans_fond_contours_epais.webp') }}">
    <meta property="twitter:creator" content="@lucas_shaya">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ route('realisations') }}">
@endpush

@push('vite')
    @vite(['resources/js/realisations.js'])
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "ItemList",
      "name": "Portfolio et Réalisations BrightShell",
      "description": "Portfolio des réalisations web développées par BrightShell : applications web complètes, systèmes de gestion, et projets personnels.",
      "itemListElement": [
        {
          "@@type": "SoftwareApplication",
          "name": "AlloTata",
          "url": "https://allotata.fr",
          "applicationCategory": "BusinessApplication",
          "description": "Plateforme tout-en-un pour gérer agenda, clientèle, finances pour micro-entreprises",
          "operatingSystem": "Web"
        },
        {
          "@@type": "SoftwareApplication",
          "name": "Takeoff",
          "url": "https://takeoff.aeroclubmarcillacestuaire.fr",
          "applicationCategory": "BusinessApplication",
          "description": "Système de gestion de flotte d'avions, rendez-vous, cours et ressources pour aéroclub",
          "operatingSystem": "Web"
        },
        {
          "@@type": "SoftwareApplication",
          "name": "Lotixam",
          "url": "https://lotixam.fr",
          "applicationCategory": "BusinessApplication",
          "description": "Plateforme de gestion immobilière et relation client",
          "operatingSystem": "Web"
        }
      ],
      "creator": {
        "@@type": "Person",
        "name": "Lucas ESPINAR",
        "worksFor": {
          "@@type": "Organization",
          "name": "BrightShell"
        }
      }
    }
    </script>
@endpush

@extends('layouts.app')

@php
    $backgroundMinimal = true;
    $mainClass = 'realisations-main';
@endphp

@section('content')
    <h1 class="realisations-title clipped">Réalisations</h1>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-button active" data-tab="websites">Sites Web</button>
        <button class="tab-button" data-tab="personal">Réalisations Perso</button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Websites Tab -->
        <div id="websites-tab" class="tab-pane active">
            <div class="projects-grid">
                @foreach ($websites as $project)
                <div class="project-card">
                    @if (!empty($project['image']))
                        <div class="project-screenshot" style="background-image:url('{{ asset($project['image']) }}');background-size:cover;background-position:center top;">
                        </div>
                    @else
                        <div class="project-screenshot" data-url="{{ $project['url'] ?? '' }}">
                            <div class="screenshot-placeholder">
                                <span>Chargement...</span>
                            </div>
                        </div>
                    @endif
                    <div class="project-info">
                        <h2 class="project-title">{{ $project['title'] }}</h2>
                        <p class="project-description">{{ $project['description'] }}</p>
                        @if (!empty($project['url']))
                            <a href="{{ $project['url'] }}" target="_blank" rel="noopener" class="project-link">Visiter le site →</a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Personal Projects Tab -->
        <div id="personal-tab" class="tab-pane">
            <div class="personal-projects-grid">
                @foreach ($personal as $project)
                <div class="personal-project-card">
                    <div class="personal-project-preview">
                        @if (!empty($project['image']))
                            <img src="{{ asset($project['image']) }}" alt="{{ $project['title'] }}" class="w-full h-full object-cover rounded">
                        @elseif (!empty($project['preview_id']))
                            <div class="preview-container" id="{{ $project['preview_id'] }}"></div>
                        @endif
                    </div>
                    <div class="personal-project-info">
                        <h2 class="personal-project-title">{{ $project['title'] }}</h2>
                        <p class="personal-project-description">{{ $project['description'] }}</p>
                        @if (!empty($project['demo_url']))
                        <div class="personal-project-actions">
                            <a href="{{ asset(ltrim($project['demo_url'], '/')) }}" target="_blank" class="test-link">Tester →</a>
                            <button class="copy-link-btn" data-copy-url="{{ asset(ltrim($project['demo_url'], '/')) }}">Copier le lien</button>
                        </div>
                        <div class="copy-area" style="display: none;">
                            <input type="text" readonly value="{{ asset(ltrim($project['demo_url'], '/')) }}" class="copy-input">
                            <button class="copy-btn">Copier</button>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('bottom-nav')
    <a href="{{ route('cv') }}" class="bottom-link">CV</a>
    <a href="{{ route('home') }}" class="bottom-link">Accueil</a>
@endsection
