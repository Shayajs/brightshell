@php($props = $module['props'] ?? [])

<section class="quesako-module">
    @if(!empty($props['title']))
        <h2 class="quesako-section-title">{{ $props['title'] }}</h2>
    @endif
    @if(!empty($props['imageUrl']))
        <figure class="quesako-media">
            <img src="{{ $props['imageUrl'] }}" alt="{{ $props['caption'] ?? 'Illustration' }}">
            @if(!empty($props['caption']))
                <figcaption>{{ $props['caption'] }}</figcaption>
            @endif
        </figure>
    @endif
</section>
