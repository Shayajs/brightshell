@php
    $competenceIdCounter = 0;
@endphp

<div class="competences-container" id="competences-container">
    @if(isset($competences['langages_preferes']) && is_array($competences['langages_preferes']) && count($competences['langages_preferes']) > 0)
        <div class="competences-group">
            <div class="competences-group-title">Langages Préférés</div>
            @foreach(collect($competences['langages_preferes'])->sortBy('priorite') as $comp)
                @php $competenceId = 'competence-' . $competenceIdCounter++; @endphp
                <div class="competence-item" data-competence-id="{{ $competenceId }}" data-competence-name="{{ $comp['nom'] }}" @if(isset($comp['emoji']))data-emoji="{{ $comp['emoji'] }}"@endif @if(isset($comp['color']))data-color="{{ $comp['color'] }}"@endif>
                    <div class="competence-header">
                        <span class="competence-name">{{ $comp['nom'] }}</span>
                        <span class="competence-level">{{ $comp['niveau'] ?? 0 }}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: {{ $comp['niveau'] ?? 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(isset($competences['langages_maitrises']) && is_array($competences['langages_maitrises']) && count($competences['langages_maitrises']) > 0)
        <div class="competences-group">
            <div class="competences-group-title">Langages Maîtrisés</div>
            @foreach(collect($competences['langages_maitrises'])->sortBy('priorite') as $comp)
                @php $competenceId = 'competence-' . $competenceIdCounter++; @endphp
                <div class="competence-item" data-competence-id="{{ $competenceId }}" data-competence-name="{{ $comp['nom'] }}" @if(isset($comp['emoji']))data-emoji="{{ $comp['emoji'] }}"@endif @if(isset($comp['color']))data-color="{{ $comp['color'] }}"@endif>
                    <div class="competence-header">
                        <span class="competence-name">{{ $comp['nom'] }}</span>
                        <span class="competence-level">{{ $comp['niveau'] ?? 0 }}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: {{ $comp['niveau'] ?? 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(isset($competences['frameworks']) && is_array($competences['frameworks']) && count($competences['frameworks']) > 0)
        <div class="competences-group">
            <div class="competences-group-title">Frameworks & Bibliothèques</div>
            @foreach(collect($competences['frameworks'])->sortBy('priorite') as $comp)
                @php $competenceId = 'competence-' . $competenceIdCounter++; @endphp
                <div class="competence-item" data-competence-id="{{ $competenceId }}" data-competence-name="{{ $comp['nom'] }}" @if(isset($comp['emoji']))data-emoji="{{ $comp['emoji'] }}"@endif @if(isset($comp['color']))data-color="{{ $comp['color'] }}"@endif>
                    <div class="competence-header">
                        <span class="competence-name">{{ $comp['nom'] }}</span>
                        <span class="competence-level">{{ $comp['niveau'] ?? 0 }}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: {{ $comp['niveau'] ?? 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(isset($competences['bases_de_donnees']) && is_array($competences['bases_de_donnees']) && count($competences['bases_de_donnees']) > 0)
        <div class="competences-group">
            <div class="competences-group-title">Bases de Données</div>
            @foreach(collect($competences['bases_de_donnees'])->sortBy('priorite') as $comp)
                @php $competenceId = 'competence-' . $competenceIdCounter++; @endphp
                <div class="competence-item" data-competence-id="{{ $competenceId }}" data-competence-name="{{ $comp['nom'] }}" @if(isset($comp['emoji']))data-emoji="{{ $comp['emoji'] }}"@endif @if(isset($comp['color']))data-color="{{ $comp['color'] }}"@endif>
                    <div class="competence-header">
                        <span class="competence-name">{{ $comp['nom'] }}</span>
                        <span class="competence-level">{{ $comp['niveau'] ?? 0 }}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: {{ $comp['niveau'] ?? 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(isset($competences['outils_devops']) && is_array($competences['outils_devops']) && count($competences['outils_devops']) > 0)
        <div class="competences-group">
            <div class="competences-group-title">Outils DevOps</div>
            @foreach(collect($competences['outils_devops'])->sortBy('priorite') as $comp)
                @php $competenceId = 'competence-' . $competenceIdCounter++; @endphp
                <div class="competence-item" data-competence-id="{{ $competenceId }}" data-competence-name="{{ $comp['nom'] }}" @if(isset($comp['emoji']))data-emoji="{{ $comp['emoji'] }}"@endif @if(isset($comp['color']))data-color="{{ $comp['color'] }}"@endif>
                    <div class="competence-header">
                        <span class="competence-name">{{ $comp['nom'] }}</span>
                        <span class="competence-level">{{ $comp['niveau'] ?? 0 }}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: {{ $comp['niveau'] ?? 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(isset($competences['langages_connus']) && is_array($competences['langages_connus']) && count($competences['langages_connus']) > 0)
        <div class="competences-group">
            <div class="competences-group-title">Langages Connus</div>
            <div class="competences-tags">
                @foreach(collect($competences['langages_connus'])->sortBy('priorite') as $comp)
                    <span class="competence-tag">{{ $comp['nom'] }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(isset($competences['langues']) && is_array($competences['langues']) && count($competences['langues']) > 0)
        <div class="competences-group">
            <div class="competences-group-title">Langues</div>
            @foreach($competences['langues'] as $langue)
                @php $competenceId = 'competence-' . $competenceIdCounter++; @endphp
                <div class="competence-item" data-competence-id="{{ $competenceId }}" data-competence-name="{{ $langue['nom'] }}" @if(isset($langue['emoji']))data-emoji="{{ $langue['emoji'] }}"@endif>
                    <div class="competence-header">
                        <span class="competence-name">{{ $langue['nom'] }}</span>
                        <span class="competence-level">{{ $langue['niveau'] ?? '' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
