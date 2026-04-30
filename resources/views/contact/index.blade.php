@use('App\Models\ContactMessage')
@use('App\Support\BrightshellDomain')

@extends('layouts.auth')

@section('title', 'Contact')

@php
    $allowedTypes = array_keys(ContactMessage::typeChoices());
    $oldType = old('type');
    if (is_string($oldType) && in_array($oldType, $allowedTypes, true)) {
        $activeType = $oldType;
    }
    $activeType = $activeType ?? ContactMessage::TYPE_GENERAL;
    $panelClass = $activeType === ContactMessage::TYPE_PROJECT ? 'auth-shell__panel--full' : '';

    $typeIntros = [
        ContactMessage::TYPE_GENERAL => 'Une question, une envie d’en savoir plus ? Écrivez-moi quelques lignes.',
        ContactMessage::TYPE_PROFESSIONAL => 'Une opportunité, un partenariat, une mission : présentez votre besoin.',
        ContactMessage::TYPE_COMPLAINT => 'Un souci, un point bloquant ? Expliquez ce qui s’est passé, je reviens vers vous.',
        ContactMessage::TYPE_PROJECT => 'Décrivez votre projet en profondeur : Markdown, pièces jointes, budget, délais.',
    ];
@endphp

@section('auth_panel_class', $panelClass)

@section('content')
    <a href="{{ BrightshellDomain::publicSiteUrl() }}" class="auth-back-link">← Retour au site</a>

    @include('layouts.partials.flash')

    <h1 class="auth-title">Prendre contact</h1>
    <p class="auth-subtitle" data-contact-subtitle>{{ $typeIntros[$activeType] }}</p>

    <div
        class="contact-form-root"
        data-contact-root
        data-contact-active-type="{{ $activeType }}"
        data-contact-preview-url="{{ route('contact.markdown.preview') }}"
        data-contact-csrf="{{ csrf_token() }}"
    >
        @include('contact.partials.type-picker', ['activeType' => $activeType, 'typeIntros' => $typeIntros])

        <section class="contact-panel" data-contact-panel="{{ ContactMessage::TYPE_GENERAL }}" @if($activeType !== ContactMessage::TYPE_GENERAL) hidden @endif>
            @include('contact.partials.form-general', ['prefillUser' => $prefillUser, 'activeType' => $activeType])
        </section>

        <section class="contact-panel" data-contact-panel="{{ ContactMessage::TYPE_PROFESSIONAL }}" @if($activeType !== ContactMessage::TYPE_PROFESSIONAL) hidden @endif>
            @include('contact.partials.form-professional', ['prefillUser' => $prefillUser, 'activeType' => $activeType])
        </section>

        <section class="contact-panel" data-contact-panel="{{ ContactMessage::TYPE_COMPLAINT }}" @if($activeType !== ContactMessage::TYPE_COMPLAINT) hidden @endif>
            @include('contact.partials.form-complaint', ['prefillUser' => $prefillUser, 'activeType' => $activeType])
        </section>

        <section class="contact-panel" data-contact-panel="{{ ContactMessage::TYPE_PROJECT }}" @if($activeType !== ContactMessage::TYPE_PROJECT) hidden @endif>
            @include('contact.partials.form-project', ['prefillUser' => $prefillUser, 'activeType' => $activeType])
        </section>
    </div>

@endsection

@push('vite')
    @vite(['resources/js/contact-form.js'])
@endpush
