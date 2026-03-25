@php($props = $module['props'] ?? [])

<section class="quesako-module">
    <div class="quesako-divider">
        @if(!empty($props['label']))
            <span>{{ $props['label'] }}</span>
        @endif
    </div>
</section>
