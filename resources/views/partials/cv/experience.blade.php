@php
    $INITIAL_DISPLAY = 3;
    $hasMore = count($experience) > $INITIAL_DISPLAY;
@endphp

<div class="timeline" id="experience-timeline">
    @foreach($experience as $index => $exp)
        <div class="timeline-item {{ $index >= $INITIAL_DISPLAY ? 'timeline-item-hidden' : '' }}">
            <div class="timeline-date">
                @if(isset($exp['date_fin']) && strtolower($exp['date_fin']) === 'présent')
                    {{ $exp['date_debut'] ?? '' }} - Présent
                @elseif(isset($exp['date_debut']) && isset($exp['date_fin']))
                    {{ $exp['date_debut'] }} - {{ $exp['date_fin'] }}
                @else
                    {{ $exp['date_debut'] ?? '' }}
                @endif
            </div>
            <div class="timeline-title">{{ $exp['poste'] ?? '' }}</div>
            <div class="timeline-subtitle">
                {{ $exp['entreprise'] ?? '' }}{{ isset($exp['lieu']) ? ' - ' . $exp['lieu'] : '' }}
            </div>
            
            @if(isset($exp['description']))
                <div class="timeline-description">{{ $exp['description'] }}</div>
            @endif
            
            @if(isset($exp['realisations']) && is_array($exp['realisations']) && count($exp['realisations']) > 0)
                <ul class="timeline-realisations">
                    @foreach($exp['realisations'] as $real)
                        <li class="timeline-realisation-item">{{ $real }}</li>
                    @endforeach
                </ul>
            @endif
            
            @if(isset($exp['technologies']) && is_array($exp['technologies']) && count($exp['technologies']) > 0)
                <div class="timeline-technologies">
                    @foreach($exp['technologies'] as $tech)
                        <span class="timeline-tech-tag" data-tech-name="{{ $tech }}">{{ $tech }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
    
    @if($hasMore)
        <button class="timeline-toggle-btn" data-target="experience-timeline" aria-expanded="false">
            Voir plus ({{ count($experience) - $INITIAL_DISPLAY }})
        </button>
    @endif
</div>
