@php($props = $module['props'] ?? [])

<section class="quesako-module quesako-cta">
    @if(!empty($props['title']))
        <h2 class="quesako-section-title">{{ $props['title'] }}</h2>
    @endif
    @if(!empty($props['body']))
        <p class="quesako-section-text">{{ $props['body'] }}</p>
    @endif
    @if(!empty($props['buttonLabel']) && !empty($props['buttonUrl']))
        <p class="mt-3">
            <a href="{{ $props['buttonUrl'] }}" class="quesako-cta-btn">{{ $props['buttonLabel'] }}</a>
        </p>
    @endif
</section>
