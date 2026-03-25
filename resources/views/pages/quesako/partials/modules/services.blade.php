@php($props = $module['props'] ?? [])

<section class="quesako-module">
    <h2 class="quesako-section-title">{{ $props['title'] ?? 'Services' }}</h2>
    @if(!empty($props['items']) && is_array($props['items']))
        <ul class="quesako-list">
            @foreach($props['items'] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @endif
</section>
