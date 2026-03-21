@push('head')
    <!-- Primary Meta Tags -->
    <title>Services de Développement Web | BrightShell - Full Stack, DevOps, SEO</title>
    <meta name="title" content="Services de Développement Web | BrightShell - Full Stack, DevOps, SEO">
    <meta name="description" content="BrightShell offre une gamme complète de services de développement web : développement full stack PHP/Symfony/Laravel, systèmes de gestion sur mesure, bases de données PostgreSQL/MySQL, design UI/UX moderne, DevOps Docker, optimisation SEO et maintenance. Solutions adaptées à vos besoins spécifiques.">
    <meta name="keywords" content="services développement web, développement full stack, PHP Symfony Laravel, systèmes de gestion, bases de données PostgreSQL MySQL, design UI UX, DevOps Docker, optimisation SEO, maintenance applicative, développement sur mesure, APIs REST, applications web, France">
    <meta name="author" content="BrightShell - Lucas ESPINAR">
    <meta name="robots" content="index, follow">
    <meta name="language" content="French">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('services') }}">
    <meta property="og:title" content="Services de Développement Web | BrightShell - Full Stack, DevOps, SEO">
    <meta property="og:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="og:image" content="{{ asset('img/logo_sans_fond_contours_epais.webp') }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="BrightShell">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ route('services') }}">
    <meta property="twitter:title" content="Services de Développement Web | BrightShell - Full Stack, DevOps, SEO">
    <meta property="twitter:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="twitter:image" content="{{ asset('img/logo_sans_fond_contours_epais.webp') }}">
    <meta property="twitter:creator" content="@lucas_shaya">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ route('services') }}">
@endpush

@push('styles')
    @vite(['resources/css/pages/services.css'])
@endpush

@push('vite')
    @vite(['resources/js/app.js'])
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Service",
      "serviceType": "Développement Web Full Stack",
      "provider": {
        "@type": "LocalBusiness",
        "name": "BrightShell",
        "url": "{{ url('/') }}",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "Bussac-Forêt",
          "addressCountry": "FR"
        }
      },
      "areaServed": "FR",
      "description": "Services complets de développement web : développement full stack PHP/Symfony/Laravel, systèmes de gestion sur mesure, bases de données PostgreSQL/MySQL, design UI/UX moderne, DevOps Docker, optimisation SEO et maintenance.",
      "hasOfferCatalog": {
        "@type": "OfferCatalog",
        "name": "Services de Développement Web",
        "itemListElement": [
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Développement Web Full Stack",
              "description": "Applications web sur mesure avec PHP/Symfony, Laravel, JavaScript, TypeScript"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Systèmes de Gestion",
              "description": "Applications de gestion sur mesure pour automatiser vos processus métier"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Bases de Données & Backend",
              "description": "Conception et optimisation de bases de données robustes avec PostgreSQL, MySQL"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Design & Interface Utilisateur",
              "description": "Design moderne et intuitif pour une expérience utilisateur exceptionnelle"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Intégration & DevOps",
              "description": "Configuration Docker, intégration d'outils CRM, mise en place d'environnements"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "SEO & Optimisation",
              "description": "Optimisation pour les moteurs de recherche et amélioration des performances"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Maintenance & Support",
              "description": "Accompagnement continu pour maintenir et faire évoluer vos applications"
            }
          }
        ]
      }
    }
    </script>
@endpush

@extends('layouts.app')

@php
    $backgroundMinimal = true;
    $mainClass = 'services-main';
@endphp

@section('content')
    <h1 class="services-title clipped">Services</h1>

    <!-- Introduction Section -->
    <section class="services-intro-section">
        <p class="services-intro-text">
            Solutions innovantes et design ultra-moderne pour vos projets. 
            Développement sur mesure adapté à vos besoins spécifiques.
        </p>
    </section>

    <!-- Services Grid -->
    <section class="services-grid-section">
        <div class="services-grid">
            
            <!-- Développement Web Full Stack -->
            <div class="service-card">
                <div class="service-icon">⚡</div>
                <h2 class="service-title">Développement Web Full Stack</h2>
                <p class="service-description">
                    Applications web sur mesure développées avec les technologies modernes. 
                    Du frontend interactif au backend robuste, création de solutions complètes 
                    et performantes adaptées à vos besoins.
                </p>
                <ul class="service-features">
                    <li>Applications PHP/Symfony et Laravel</li>
                    <li>Interfaces JavaScript/TypeScript modernes</li>
                    <li>Architecture T3 Stack</li>
                    <li>Sites vitrines et plateformes complexes</li>
                </ul>
            </div>

            <!-- Systèmes de Gestion -->
            <div class="service-card">
                <div class="service-icon">📊</div>
                <h2 class="service-title">Systèmes de Gestion</h2>
                <p class="service-description">
                    Applications de gestion sur mesure pour automatiser vos processus métier. 
                    Solutions adaptées à votre activité pour optimiser votre productivité 
                    et centraliser vos données.
                </p>
                <ul class="service-features">
                    <li>Gestion d'agenda et de clientèle</li>
                    <li>Plateformes de gestion immobilière</li>
                    <li>Solutions de gestion de flotte</li>
                    <li>Systèmes de gestion d'entreprise</li>
                </ul>
            </div>

            <!-- Bases de Données & Backend -->
            <div class="service-card">
                <div class="service-icon">🗄️</div>
                <h2 class="service-title">Bases de Données & Backend</h2>
                <p class="service-description">
                    Conception et optimisation de bases de données robustes. 
                    Architecture backend scalable et APIs REST pour vos applications.
                </p>
                <ul class="service-features">
                    <li>PostgreSQL, MySQL/MariaDB</li>
                    <li>Architecture backend robuste</li>
                    <li>APIs REST</li>
                    <li>Optimisation et performance</li>
                </ul>
            </div>

            <!-- Design & Interface Utilisateur -->
            <div class="service-card">
                <div class="service-icon">🎨</div>
                <h2 class="service-title">Design & Interface Utilisateur</h2>
                <p class="service-description">
                    Design moderne et intuitif pour une expérience utilisateur exceptionnelle. 
                    Interfaces responsive et élégantes qui s'adaptent à tous les appareils.
                </p>
                <ul class="service-features">
                    <li>Design responsive et moderne</li>
                    <li>Interfaces utilisateur intuitives</li>
                    <li>Intégration CSS/HTML avancée</li>
                    <li>Expérience utilisateur optimisée</li>
                </ul>
            </div>

            <!-- Intégration & DevOps -->
            <div class="service-card">
                <div class="service-icon">🔧</div>
                <h2 class="service-title">Intégration & DevOps</h2>
                <p class="service-description">
                    Configuration et mise en place d'environnements de développement et de production. 
                    Intégration d'outils tiers et optimisation de vos workflows.
                </p>
                <ul class="service-features">
                    <li>Configuration Docker</li>
                    <li>Intégration d'outils CRM</li>
                    <li>Mise en place d'environnements</li>
                    <li>Automatisation de workflows</li>
                </ul>
            </div>

            <!-- SEO & Optimisation -->
            <div class="service-card">
                <div class="service-icon">🚀</div>
                <h2 class="service-title">SEO & Optimisation</h2>
                <p class="service-description">
                    Optimisation pour les moteurs de recherche et amélioration des performances. 
                    Amélioration de la visibilité et de la vitesse de chargement de vos sites.
                </p>
                <ul class="service-features">
                    <li>Optimisation SEO</li>
                    <li>Performance et vitesse</li>
                    <li>Analyse et amélioration</li>
                    <li>Référencement naturel</li>
                </ul>
            </div>

            <!-- Maintenance & Support -->
            <div class="service-card">
                <div class="service-icon">🛠️</div>
                <h2 class="service-title">Maintenance & Support</h2>
                <p class="service-description">
                    Accompagnement continu pour maintenir et faire évoluer vos applications. 
                    Support technique et mises à jour pour garantir la pérennité de vos projets.
                </p>
                <ul class="service-features">
                    <li>Maintenance continue</li>
                    <li>Support technique</li>
                    <li>Mises à jour et évolutions</li>
                    <li>Suivi et optimisation</li>
                </ul>
            </div>

        </div>
    </section>
@endsection

@section('bottom-nav')
    <a href="{{ route('cv') }}" class="bottom-link">CV</a>
    <a href="{{ route('realisations') }}" class="bottom-link">Réalisations</a>
    <a href="{{ route('home') }}" class="bottom-link">Accueil</a>
@endsection
