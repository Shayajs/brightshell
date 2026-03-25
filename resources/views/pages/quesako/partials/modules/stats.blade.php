@php($props = $module['props'] ?? [])

<section class="quesako-module">
    @if(!empty($props['title']))
        <h2 class="quesako-section-title">{{ $props['title'] }}</h2>
    @endif
    @if(!empty($props['items']) && is_array($props['items']))
        <div class="quesako-stats">
            @foreach($props['items'] as $item)
                <article>
                    <strong>{{ $item['value'] ?? '' }}</strong>
                    <span>{{ $item['label'] ?? '' }}</span>
                </article>
            @endforeach
        </div>
    @endif
</section>
