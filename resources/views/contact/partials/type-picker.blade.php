@use('App\Models\ContactMessage')

@php
    $cards = [
        ContactMessage::TYPE_GENERAL => [
            'label' => 'Curiosité',
            'tagline' => 'Une question rapide, prendre des nouvelles.',
            'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        ],
        ContactMessage::TYPE_PROFESSIONAL => [
            'label' => 'Projet professionnel',
            'tagline' => 'Une opportunité, un partenariat, une mission.',
            'icon' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
        ],
        ContactMessage::TYPE_COMPLAINT => [
            'label' => 'Réclamation',
            'tagline' => 'Un souci à signaler, une demande de support.',
            'icon' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        ],
        ContactMessage::TYPE_PROJECT => [
            'label' => 'Soumettre un projet',
            'tagline' => 'Brief détaillé, Markdown, pièces jointes.',
            'icon' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>',
        ],
    ];
@endphp

<div class="contact-type-grid" role="tablist" aria-label="Type de demande">
    @foreach ($cards as $key => $card)
        <button
            type="button"
            class="contact-type-card"
            data-contact-type-trigger="{{ $key }}"
            role="tab"
            aria-selected="{{ $activeType === $key ? 'true' : 'false' }}"
            aria-controls="contact-panel-{{ $key }}"
        >
            <span class="contact-type-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $card['icon'] !!}</svg>
            </span>
            <span class="contact-type-card__body">
                <span class="contact-type-card__label">{{ $card['label'] }}</span>
                <span class="contact-type-card__tagline">{{ $card['tagline'] }}</span>
            </span>
        </button>
    @endforeach
</div>
