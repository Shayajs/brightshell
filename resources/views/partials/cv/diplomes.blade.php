@php
    $INITIAL_DISPLAY = 3;
    $hasMore = count($diplomes) > $INITIAL_DISPLAY;
@endphp

<div class="timeline" id="diplomes-timeline">
    @foreach($diplomes as $index => $diplome)
        <div class="timeline-item {{ $index >= $INITIAL_DISPLAY ? 'timeline-item-hidden' : '' }}">
            <div class="timeline-date">{{ $diplome['date'] ?? '' }}</div>
            <div class="timeline-title">{{ $diplome['diplome'] ?? '' }}</div>
            <div class="timeline-subtitle">
                {{ $diplome['etablissement'] ?? '' }}{{ isset($diplome['lieu']) ? ' - ' . $diplome['lieu'] : '' }}
            </div>
            
            @if(isset($diplome['details']) && is_array($diplome['details']) && count($diplome['details']) > 0)
                <ul class="timeline-details">
                    @foreach($diplome['details'] as $detail)
                        <li class="timeline-detail-item">{{ $detail }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
    
    @if($hasMore)
        <button class="timeline-toggle-btn" data-target="diplomes-timeline" aria-expanded="false">
            Voir plus ({{ count($diplomes) - $INITIAL_DISPLAY }})
        </button>
    @endif
</div>
