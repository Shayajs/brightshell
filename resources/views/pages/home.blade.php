@push('head')
    <!-- Primary Meta Tags -->
    <title>BrightShell - Développement Web Full Stack | Solutions Innovantes</title>
    <meta name="title" content="BrightShell - Développement Web Full Stack | Solutions Innovantes">
    <meta name="description" content="BrightShell propose des solutions de développement web full stack sur mesure. Développement d'applications web modernes, systèmes de gestion, bases de données et interfaces utilisateur innovantes. Expertise en PHP, Symfony, JavaScript, TypeScript et architecture T3 Stack.">
    <meta name="keywords" content="développement web, full stack, PHP, Symfony, Laravel, JavaScript, TypeScript, développement sur mesure, applications web, systèmes de gestion, bases de données, interface utilisateur, SEO, DevOps, Bussac-Forêt, France">
    <meta name="author" content="BrightShell - Lucas ESPINAR">
    <meta name="robots" content="index, follow">
    <meta name="language" content="French">
    <meta name="revisit-after" content="7 days">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="BrightShell - Développement Web Full Stack | Solutions Innovantes">
    <meta property="og:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="og:image" content="{{ asset('img/logo_sans_fond_contours_epais.webp') }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="BrightShell">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="BrightShell - Développement Web Full Stack | Solutions Innovantes">
    <meta property="twitter:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="twitter:image" content="{{ asset('img/logo_sans_fond_contours_epais.webp') }}">
    <meta property="twitter:creator" content="@lucas_shaya">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">
    
    <style>
        @font-face {
            font-family: 'Gilroy ExtraBold';
            src: url('{{ asset('fonts/Gilroy-ExtraBold.otf') }}') format('opentype');
            font-weight: 800;
            font-style: normal;
        }
    </style>
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "BrightShell",
      "description": "Solutions de développement web full stack sur mesure. Applications web modernes, systèmes de gestion, bases de données et interfaces utilisateur innovantes.",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('img/logo_sans_fond_contours_epais.webp') }}",
      "image": "{{ asset('img/logo_sans_fond_contours_epais.webp') }}",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Bussac-Forêt",
        "addressCountry": "FR"
      },
      "founder": {
        "@type": "Person",
        "name": "Lucas ESPINAR"
      },
      "areaServed": "FR",
      "serviceType": "Développement Web Full Stack",
      "priceRange": "€€"
    }
    </script>
@endpush

@extends('layouts.app')

@section('content')
    <!-- Logo -->
    <div class="logo-container">
        <img src="{{ asset('img/logo_sans_fond_contours_epais.webp') }}" alt="Logo BrightShell - Développement Web Full Stack - Solutions innovantes" class="main-logo">
    </div>

    <!-- Brand Name with wireframe typography -->
    <div class="brand-name" id="brand-name-clipped">
        <!-- Le texte clippé sera généré ici par JavaScript -->
    </div>
@endsection

@push('vite')
    @vite(['resources/js/app.js'])
@endpush

@section('bottom-nav')
    <a href="{{ route('cv') }}" class="bottom-link">CV</a>
    <a href="{{ route('realisations') }}" class="bottom-link">Réalisations</a>
@endsection

@php
    $showNavLinks = true;
    $showScrollIndicator = true;
@endphp
