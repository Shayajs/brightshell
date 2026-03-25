@php
    $seoTitle = $config['settings']['seoTitle'] ?? 'Quesako - BrightShell';
    $seoDescription = $config['settings']['seoDescription'] ?? 'Qui je suis et ce que je fais.';
    $hideTopNav = true;
@endphp

@push('head')
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('quesako.index') }}">
@endpush

@extends('layouts.app', ['bodyClass' => 'quesako-vitrine'])

@section('content')
    <section class="quesako-shell" data-quesako-shell>
        <div class="quesako-corner quesako-corner--left">BRIGHTSHELL</div>
        <div class="quesako-corner quesako-corner--right">BRIGHTSHELL</div>

        <div class="quesako-content quesako-content--left">
            @if(($activeTab['slug'] ?? '') === ($config['settings']['defaultTabSlug'] ?? ''))
                <h1 class="quesako-main-brand">BRIGHTSHELL</h1>
            @endif
            @forelse($modules as $module)
                @includeIf('pages.quesako.partials.modules.'.$module['type'], ['module' => $module])
            @empty
                <section class="quesako-module">
                    <h2>Contenu en preparation</h2>
                    <p>Cette section sera disponible tres bientot.</p>
                </section>
            @endforelse
        </div>
    </section>
@endsection

@section('bottom-nav')
    <a href="{{ route('home') }}" class="bottom-link" data-transition-link>Accueil</a>
    @foreach($tabs as $tab)
        <a href="{{ route('quesako.tab', ['tabSlug' => $tab['slug']]) }}" class="bottom-link" data-transition-link>{{ $tab['label'] }}</a>
    @endforeach
    <a href="#" class="bottom-link">Mentions légales</a>
    <a href="#" class="bottom-link">Cookies</a>
    <a href="#" class="bottom-link">Confidentialité</a>
@endsection

@push('vite')
    @vite(['resources/css/quesako.css', 'resources/js/quesako-public.js'])
@endpush
