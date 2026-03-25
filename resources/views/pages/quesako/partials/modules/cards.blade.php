@php($props = $module['props'] ?? [])

<section class="quesako-module">
    @if(!empty($props['title']))
        <h2 class="quesako-section-title">{{ $props['title'] }}</h2>
    @endif
    @if(!empty($props['cards']) && is_array($props['cards']))
        <div class="quesako-cards">
            @foreach($props['cards'] as $card)
                <article>
                    <h3>{{ $card['title'] ?? '' }}</h3>
                    <p>{{ $card['text'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    @endif
</section>
