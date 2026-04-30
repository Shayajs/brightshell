@use('App\Models\ContactMessage')

<form
    method="post"
    action="{{ route('contact.store') }}"
    class="auth-form"
    data-contact-form="{{ ContactMessage::TYPE_COMPLAINT }}"
    novalidate
>
    @csrf
    <input type="hidden" name="type" value="{{ ContactMessage::TYPE_COMPLAINT }}">
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label for="cmp_first_name" class="auth-label">Prénom</label>
            <input id="cmp_first_name" type="text" name="first_name" value="{{ old('first_name', $prefillUser?->first_name) }}" required autocomplete="given-name" class="auth-input">
            @if($activeType === ContactMessage::TYPE_COMPLAINT)
                @error('first_name') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
        <div>
            <label for="cmp_last_name" class="auth-label">Nom</label>
            <input id="cmp_last_name" type="text" name="last_name" value="{{ old('last_name', $prefillUser?->last_name) }}" required autocomplete="family-name" class="auth-input">
            @if($activeType === ContactMessage::TYPE_COMPLAINT)
                @error('last_name') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label for="cmp_email" class="auth-label">E-mail</label>
            <input id="cmp_email" type="email" name="email" value="{{ old('email', $prefillUser?->email) }}" required autocomplete="email" class="auth-input">
            @if($activeType === ContactMessage::TYPE_COMPLAINT)
                @error('email') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
        <div>
            <label for="cmp_reference" class="auth-label">Référence <span class="contact-optional">(facture, commande, projet…)</span></label>
            <input id="cmp_reference" type="text" name="reference" value="{{ old('reference') }}" class="auth-input">
            @if($activeType === ContactMessage::TYPE_COMPLAINT)
                @error('reference') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>

    <div>
        <label for="cmp_subject" class="auth-label">Sujet de la réclamation</label>
        <input id="cmp_subject" type="text" name="subject" value="{{ old('subject') }}" required class="auth-input" placeholder="En quelques mots…">
        @if($activeType === ContactMessage::TYPE_COMPLAINT)
            @error('subject') <p class="auth-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <div>
        <label for="cmp_body" class="auth-label">Description du problème</label>
        <textarea id="cmp_body" name="body" rows="8" required maxlength="4000" class="auth-input contact-textarea" placeholder="Détaillez ce qui s’est passé, ce que vous attendiez, et ce qui devrait être corrigé.">{{ old('body') }}</textarea>
        @if($activeType === ContactMessage::TYPE_COMPLAINT)
            @error('body') <p class="auth-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <button type="submit" class="auth-submit">Envoyer ma réclamation</button>
</form>
