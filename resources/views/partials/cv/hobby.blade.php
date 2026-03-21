<div class="hobby-grid" id="hobby-grid">
    @foreach($hobby as $h)
        <div class="hobby-card">
            <h3 class="hobby-title">{{ $h['nom'] ?? '' }}</h3>
            <p class="hobby-description">{{ $h['description'] ?? '' }}</p>
        </div>
    @endforeach
</div>
