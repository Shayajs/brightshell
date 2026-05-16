@props(['prospect'])

@php
    use App\Services\Prospects\Scoring\ScoreBand;
    $band = $prospect->band();
@endphp

<span class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-semibold {{ $band->badgeClasses() }}">
    @if ($band === ScoreBand::Hot)
        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
        </svg>
    @endif
    <span class="font-bold tabular-nums">{{ $prospect->score_global }}</span>
    <span class="opacity-70">{{ $band->shortLabel() }}</span>
</span>
