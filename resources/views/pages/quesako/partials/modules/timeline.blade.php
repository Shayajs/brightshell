@php($props = $module['props'] ?? [])

<section class="quesako-module">
    <h2 class="quesako-section-title">{{ $props['title'] ?? 'Parcours' }}</h2>
    @if(!empty($props['steps']) && is_array($props['steps']))
        <ol class="quesako-timeline">
            @foreach($props['steps'] as $step)
                <li>
                    <strong>{{ $step['label'] ?? '' }}</strong>
                    <p>{{ $step['text'] ?? '' }}</p>
                </li>
            @endforeach
        </ol>
    @endif
</section>
