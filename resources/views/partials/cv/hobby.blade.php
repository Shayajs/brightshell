<div class="hobby-grid" id="hobby-grid">
    @foreach($hobby as $h)
        <div class="hobby-item">
            @if(!empty($h['_image_url']))
                <div class="hobby-item-image">
                    <img src="{{ $h['_image_url'] }}"
                         alt=""
                         width="640"
                         height="360"
                         loading="lazy"
                         decoding="async">
                </div>
            @endif
            <h3 class="hobby-name">{{ $h['nom'] ?? '' }}</h3>
            <p class="hobby-description">{{ $h['description'] ?? '' }}</p>
        </div>
    @endforeach
</div>
