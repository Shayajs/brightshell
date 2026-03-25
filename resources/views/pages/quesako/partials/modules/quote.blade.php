@php($props = $module['props'] ?? [])

<section class="quesako-module quesako-quote">
    <blockquote>{{ $props['quote'] ?? '' }}</blockquote>
    @if(!empty($props['author']))
        <cite>— {{ $props['author'] }}</cite>
    @endif
</section>
