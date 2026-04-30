@use('App\Models\ContactMessage')

<form
    method="post"
    action="{{ route('contact.store') }}"
    class="auth-form"
    data-contact-form="{{ ContactMessage::TYPE_PROFESSIONAL }}"
    novalidate
>
    @csrf
    <input type="hidden" name="type" value="{{ ContactMessage::TYPE_PROFESSIONAL }}">
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label for="pro_first_name" class="auth-label">Prénom</label>
            <input id="pro_first_name" type="text" name="first_name" value="{{ old('first_name', $prefillUser?->first_name) }}" required autocomplete="given-name" class="auth-input">
            @if($activeType === ContactMessage::TYPE_PROFESSIONAL)
                @error('first_name') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
        <div>
            <label for="pro_last_name" class="auth-label">Nom</label>
            <input id="pro_last_name" type="text" name="last_name" value="{{ old('last_name', $prefillUser?->last_name) }}" required autocomplete="family-name" class="auth-input">
            @if($activeType === ContactMessage::TYPE_PROFESSIONAL)
                @error('last_name') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label for="pro_email" class="auth-label">E-mail</label>
            <input id="pro_email" type="email" name="email" value="{{ old('email', $prefillUser?->email) }}" required autocomplete="email" class="auth-input">
            @if($activeType === ContactMessage::TYPE_PROFESSIONAL)
                @error('email') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
        <div>
            <label for="pro_phone" class="auth-label">Téléphone <span class="contact-optional">(optionnel)</span></label>
            <input id="pro_phone" type="tel" name="phone" value="{{ old('phone', $prefillUser?->phone) }}" autocomplete="tel" class="auth-input">
            @if($activeType === ContactMessage::TYPE_PROFESSIONAL)
                @error('phone') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>

    <div>
        <label for="pro_company" class="auth-label">Société <span class="contact-optional">(optionnel)</span></label>
        <input id="pro_company" type="text" name="company" value="{{ old('company') }}" class="auth-input">
        @if($activeType === ContactMessage::TYPE_PROFESSIONAL)
            @error('company') <p class="auth-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <div>
        <label for="pro_subject" class="auth-label">Sujet</label>
        <input id="pro_subject" type="text" name="subject" value="{{ old('subject') }}" required class="auth-input" placeholder="Ex. Mission de 3 mois, refonte e-commerce…">
        @if($activeType === ContactMessage::TYPE_PROFESSIONAL)
            @error('subject') <p class="auth-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <div>
        <label for="pro_body" class="auth-label">Présentation de l’opportunité</label>
        <textarea id="pro_body" name="body" rows="8" required maxlength="4000" class="auth-input contact-textarea">{{ old('body') }}</textarea>
        @if($activeType === ContactMessage::TYPE_PROFESSIONAL)
            @error('body') <p class="auth-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <button type="submit" class="auth-submit">Envoyer ma demande</button>
</form>
