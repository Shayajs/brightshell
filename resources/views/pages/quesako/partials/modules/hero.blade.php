@php
    $props = $module['props'] ?? [];
    $variant = $props['animationVariant'] ?? 'fade-up';
@endphp

<section class="quesako-module quesako-hero" data-hero-variant="{{ $variant }}">
    <h1 class="quesako-hero-title">{{ $props['headline'] ?? '' }}</h1>
    @if(!empty($props['subheadline']))
        <p class="quesako-hero-subtitle">{{ $props['subheadline'] }}</p>
    @endif
</section>
