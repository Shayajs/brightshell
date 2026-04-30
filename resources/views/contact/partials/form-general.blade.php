@use('App\Models\ContactMessage')

<form
    method="post"
    action="{{ route('contact.store') }}"
    class="auth-form"
    data-contact-form="{{ ContactMessage::TYPE_GENERAL }}"
    novalidate
>
    @csrf
    <input type="hidden" name="type" value="{{ ContactMessage::TYPE_GENERAL }}">

    <input type="text" name="website" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label for="g_first_name" class="auth-label">Prénom</label>
            <input
                id="g_first_name"
                type="text"
                name="first_name"
                value="{{ old('first_name', $prefillUser?->first_name) }}"
                required
                autocomplete="given-name"
                class="auth-input"
            >
            @if($activeType === ContactMessage::TYPE_GENERAL)
                @error('first_name') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
        <div>
            <label for="g_last_name" class="auth-label">Nom</label>
            <input
                id="g_last_name"
                type="text"
                name="last_name"
                value="{{ old('last_name', $prefillUser?->last_name) }}"
                required
                autocomplete="family-name"
                class="auth-input"
            >
            @if($activeType === ContactMessage::TYPE_GENERAL)
                @error('last_name') <p class="auth-error">{{ $message }}</p> @enderror
            @endif
        </div>
    </div>

    <div>
        <label for="g_email" class="auth-label">E-mail</label>
        <input
            id="g_email"
            type="email"
            name="email"
            value="{{ old('email', $prefillUser?->email) }}"
            required
            autocomplete="email"
            class="auth-input"
        >
        @if($activeType === ContactMessage::TYPE_GENERAL)
            @error('email') <p class="auth-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <div>
        <label for="g_body" class="auth-label">Votre message</label>
        <textarea
            id="g_body"
            name="body"
            rows="6"
            required
            maxlength="1500"
            placeholder="Quelques lignes suffisent..."
            class="auth-input contact-textarea"
        >{{ old('body') }}</textarea>
        @if($activeType === ContactMessage::TYPE_GENERAL)
            @error('body') <p class="auth-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <button type="submit" class="auth-submit">Envoyer</button>
</form>
