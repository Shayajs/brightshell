@php($props = $module['props'] ?? [])

<section class="quesako-module">
    @if(!empty($props['title']))
        <h2 class="quesako-section-title">{{ $props['title'] }}</h2>
    @endif
    @if(!empty($props['items']) && is_array($props['items']))
        <div class="quesako-faq">
            @foreach($props['items'] as $item)
                <details>
                    <summary>{{ $item['question'] ?? '' }}</summary>
                    <p>{{ $item['answer'] ?? '' }}</p>
                </details>
            @endforeach
        </div>
    @endif
</section>
