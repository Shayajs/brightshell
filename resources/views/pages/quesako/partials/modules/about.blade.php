@php($props = $module['props'] ?? [])

<section class="quesako-module">
    <h2 class="quesako-section-title">{{ $props['title'] ?? 'A propos' }}</h2>
    <p class="quesako-section-text">{{ $props['body'] ?? '' }}</p>
</section>
