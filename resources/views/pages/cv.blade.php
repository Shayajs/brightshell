@push('head')
    <!-- Primary Meta Tags -->
    <title>CV & Contact | Lucas ESPINAR - Développeur Full Stack BrightShell</title>
    <meta name="title" content="CV & Contact | Lucas ESPINAR - Développeur Full Stack BrightShell">
    <meta name="description" content="CV et coordonnées de Lucas ESPINAR, développeur Full Stack auto-entrepreneur chez BrightShell. Expérience en développement web PHP/Symfony, JavaScript, bases de données, systèmes de gestion. Bussac-Forêt, France. Contactez-moi pour vos projets web.">
    <meta name="keywords" content="CV développeur full stack, Lucas ESPINAR, développeur PHP Symfony, développeur JavaScript, auto-entrepreneur développement web, BrightShell, Bussac-Forêt, contact développeur, portfolio développeur, compétences développement web, France">
    <meta name="author" content="Lucas ESPINAR - BrightShell">
    <meta name="robots" content="index, follow">
    <meta name="language" content="French">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ route('cv') }}">
    <meta property="og:title" content="CV & Contact | Lucas ESPINAR - Développeur Full Stack BrightShell">
    <meta property="og:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="og:image" content="{{ $cvPhotoUrl }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="BrightShell">
    <meta property="profile:first_name" content="Lucas">
    <meta property="profile:last_name" content="ESPINAR">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary">
    <meta property="twitter:url" content="{{ route('cv') }}">
    <meta property="twitter:title" content="CV & Contact | Lucas ESPINAR - Développeur Full Stack BrightShell">
    <meta property="twitter:description" content="Créateur de site web ou de solutions web, créateur de logiciels, venez visiter notre site internet si vous souhaitez découvrir nos solutions et nos prestations !">
    <meta property="twitter:image" content="{{ $cvPhotoUrl }}">
    <meta property="twitter:creator" content="@lucas_shaya">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ route('cv') }}">

    <link rel="preload" href="{{ $cvPhotoUrl }}" as="image">
@endpush

@push('vite')
    @vite(['resources/js/cv.js'])
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Person",
      "name": "Lucas ESPINAR",
      "image": @json($cvPhotoUrl),
      "jobTitle": "Développeur Full Stack",
      "worksFor": {
        "@@type": "Organization",
        "name": "BrightShell"
      },
      "email": "{{ $contact['etat_civil']['email'] ?? '' }}",
      "telephone": "{{ $contact['etat_civil']['telephone'] ?? '' }}",
      "url": "{{ url('/') }}",
      "sameAs": [
        "https://github.com/{{ ltrim($contact['reseaux_sociaux']['github'] ?? '', '@') }}",
        "https://www.linkedin.com/in/{{ ltrim($contact['reseaux_sociaux']['linkedin'] ?? '', '@') }}",
        "https://twitter.com/{{ ltrim($contact['reseaux_sociaux']['twitter_x'] ?? '', '@') }}"
      ],
      "address": {
        "@@type": "PostalAddress",
        "addressLocality": "{{ $contact['etat_civil']['localisation'] ?? 'Bussac-Forêt' }}",
        "addressCountry": "FR"
      },
      "description": "{{ $contact['resume_profil'] ?? 'Développeur Full Stack motivé ayant construit des outils de gestion et participé à des projets web complets pendant son parcours académique et ses missions opérationnelles.' }}",
      "knowsAbout": [
        "Développement Web Full Stack",
        "PHP",
        "Symfony",
        "Laravel",
        "JavaScript",
        "TypeScript",
        "Bases de données",
        "PostgreSQL",
        "MySQL",
        "DevOps",
        "Docker",
        "SEO"
      ]
    }
    </script>
@endpush

@extends('layouts.app')

@php
    $backgroundMinimal = true;
    $mainClass = 'cv-main';
@endphp

@section('content')
    <h1 class="cv-title clipped">CV & Contact</h1>

    <!-- Resume Section -->
    <section class="cv-resume-section" id="resume-section">
        <div class="cv-resume-text" id="resume-text">
            {{ $contact['resume_profil'] ?? '' }}
        </div>
    </section>

    <!-- Photo Section -->
    <section class="cv-photo-section">
        <div class="cv-photo-container">
            <img src="{{ $cvPhotoUrl }}"
                 alt="Lucas ESPINAR - Développeur Full Stack BrightShell - Photo de profil professionnel"
                 class="cv-photo"
                 width="440"
                 height="440"
                 decoding="async"
                 fetchpriority="high">
        </div>
    </section>

    <!-- Experience and Diplomes Timeline Container -->
    <div class="cv-timeline-container">
        <!-- Experience Timeline -->
        <section class="cv-section" id="experience-section">
            <h2 class="cv-section-title">Expérience</h2>
            @include('partials.cv.experience')
        </section>

        <!-- Diplomes Timeline -->
        <section class="cv-section" id="diplomes-section">
            <h2 class="cv-section-title">Diplômes</h2>
            @include('partials.cv.diplomes')
        </section>
    </div>

    <!-- Hobby Section -->
    <section class="cv-section" id="hobby-section">
        <h2 class="cv-section-title">Hobbies</h2>
        @include('partials.cv.hobby')
    </section>

    <!-- Competences Section -->
    <section class="cv-section" id="competences-section">
        <h2 class="cv-section-title">Compétences</h2>
        @include('partials.cv.competences')
    </section>

    <!-- Certifications Section -->
    <section class="cv-section" id="certifications-section">
        <h2 class="cv-section-title">Certifications & Bénévolat</h2>
        @include('partials.cv.certifications')
    </section>

    <!-- Contact Section -->
    <section class="cv-section" id="contact-section">
        <h2 class="cv-section-title">Contact</h2>
        @include('partials.cv.contact')
    </section>

    <!-- References Section -->
    <section class="cv-section" id="references-section">
        <h2 class="cv-section-title">Références</h2>
        @include('partials.cv.references')
    </section>
@endsection

@section('bottom-nav')
    <a href="{{ route('realisations') }}" class="bottom-link">Réalisations</a>
    <a href="{{ route('home') }}" class="bottom-link">Accueil</a>
@endsection
