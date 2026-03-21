@php
    $etatCivil = $contact['etat_civil'] ?? [];
    $reseaux = $contact['reseaux_sociaux'] ?? [];
@endphp

<div class="contact-container" id="contact-container">
    <div class="contact-info">
        <div class="contact-item">
            <strong>Email:</strong>
            <a href="mailto:{{ $etatCivil['email'] ?? '' }}">{{ $etatCivil['email'] ?? '' }}</a>
        </div>
        <div class="contact-item">
            <strong>Téléphone:</strong>
            <a href="tel:{{ str_replace(' ', '', $etatCivil['telephone'] ?? '') }}">{{ $etatCivil['telephone'] ?? '' }}</a>
        </div>
        <div class="contact-item">
            <strong>Site web:</strong>
            <a href="{{ $etatCivil['site_web'] ?? '' }}" target="_blank" rel="noopener">{{ $etatCivil['site_web'] ?? '' }}</a>
        </div>
        <div class="contact-item">
            <strong>Localisation:</strong>
            {{ $etatCivil['localisation'] ?? '' }}
        </div>
        @if(isset($etatCivil['permis']))
        <div class="contact-item">
            <strong>Permis:</strong>
            {{ $etatCivil['permis'] }}
        </div>
        @endif
    </div>
    
    <div class="contact-social">
        @if(isset($reseaux['github']))
        <div class="social-item">
            <strong>GitHub:</strong>
            <a href="https://github.com/{{ ltrim($reseaux['github'], '@') }}" target="_blank" rel="noopener">{{ $reseaux['github'] }}</a>
        </div>
        @endif
        @if(isset($reseaux['linkedin']))
        <div class="social-item">
            <strong>LinkedIn:</strong>
            <a href="https://www.linkedin.com/in/{{ ltrim($reseaux['linkedin'], '@') }}" target="_blank" rel="noopener">{{ $reseaux['linkedin'] }}</a>
        </div>
        @endif
        @if(isset($reseaux['twitter_x']))
        <div class="social-item">
            <strong>Twitter/X:</strong>
            <a href="https://twitter.com/{{ ltrim($reseaux['twitter_x'], '@') }}" target="_blank" rel="noopener">{{ $reseaux['twitter_x'] }}</a>
        </div>
        @endif
    </div>
</div>
