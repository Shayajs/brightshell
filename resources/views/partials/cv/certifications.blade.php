<div class="certifications-container" id="certifications-container">
    @foreach($certifications as $cert)
        <div class="certification-item">
            <h3 class="certification-title">{{ $cert['titre'] ?? '' }}</h3>
            @if(isset($cert['role']))
                <div class="certification-role">{{ $cert['role'] }}</div>
            @endif
            @if(isset($cert['details']))
                @if(is_array($cert['details']))
                    <ul class="certification-details">
                        @foreach($cert['details'] as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="certification-details">{{ $cert['details'] }}</div>
                @endif
            @endif
            @if(isset($cert['annees']) && is_array($cert['annees']))
                <div class="certification-years">{{ implode(' - ', $cert['annees']) }}</div>
            @endif
        </div>
    @endforeach
</div>
