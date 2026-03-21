<div class="references-container" id="references-container">
    @foreach($references as $ref)
        <div class="reference-item">
            <h3 class="reference-name">{{ $ref['nom'] ?? '' }}</h3>
            @if(isset($ref['role']))
                <div class="reference-role">{{ $ref['role'] }}</div>
            @endif
            @if(isset($ref['organisation']))
                <div class="reference-org">{{ $ref['organisation'] }}</div>
            @endif
            @if(isset($ref['telephone']))
                <div class="reference-phone">
                    <a href="tel:{{ str_replace(' ', '', $ref['telephone']) }}">{{ $ref['telephone'] }}</a>
                </div>
            @endif
        </div>
    @endforeach
</div>
