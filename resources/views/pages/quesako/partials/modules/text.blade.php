@php($props = $module['props'] ?? [])

<section class="quesako-module">
    @if(!empty($props['title']))
        <h2 class="quesako-section-title">{{ $props['title'] }}</h2>
    @endif
    @if(!empty($props['body']))
        <p class="quesako-section-text">{{ $props['body'] }}</p>
    @endif
</section>
