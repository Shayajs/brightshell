@extends('layouts.admin')

@section('title', 'Réglages — Profil')
@section('topbar_label', 'Profil')

@section('content')
    <div class="w-full min-w-0 space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Identité</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Profil</h1>
            <p class="text-sm text-zinc-400">Nom, e-mail, téléphone et notes personnelles (visibles uniquement par vous et l’équipe habilitée).</p>
        </header>

        @include('layouts.partials.flash')

        <form id="profile-settings-form" method="POST" action="{{ route('portals.settings.profile.update') }}" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <div class="flex shrink-0 flex-col items-center gap-2 sm:items-start">
                    @include('partials.user-avatar', ['user' => $user, 'size' => 'h-20 w-20', 'textSize' => 'text-2xl'])
                    <p id="avatar-client-error" class="hidden max-w-[11rem] text-center text-xs text-red-400 sm:text-left" role="alert"></p>
                </div>
                <div class="min-w-0 flex-1 space-y-3">
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Photo de profil</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="block w-full text-sm text-zinc-400 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-800 file:px-3 file:py-2 file:text-sm file:font-medium file:text-zinc-200 hover:file:bg-zinc-700">
                    @error('avatar')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                    @if ($user->avatarUrl())
                        <label class="flex cursor-pointer items-center gap-2 text-xs text-zinc-500">
                            <input type="checkbox" name="remove_avatar" value="1" class="rounded border-zinc-600 bg-zinc-950 text-indigo-500">
                            Supprimer la photo actuelle
                        </label>
                    @endif
                    <p class="text-xs text-zinc-600">JPG, PNG ou WebP — 25 Mo max. Affichée dans la barre latérale et là où votre profil est montré.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="mb-1.5 block text-sm font-medium text-zinc-300">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required autocomplete="given-name"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('first_name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="last_name" class="mb-1.5 block text-sm font-medium text-zinc-300">Nom</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required autocomplete="family-name"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('last_name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-zinc-300">Adresse e-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-zinc-300">Téléphone <span class="font-normal text-zinc-500">(facultatif)</span></label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="+33 …"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @error('phone')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="profile_notes" class="mb-1.5 block text-sm font-medium text-zinc-300">Infos importantes <span class="font-normal text-zinc-500">(facultatif)</span></label>
                <p class="mb-2 text-xs text-zinc-500">Allergies, contraintes horaires, préférences de contact — tout ce qui peut être utile côté organisation.</p>
                <textarea id="profile_notes" name="profile_notes" rows="6" placeholder="Ex. Disponible les mercredis après-midi uniquement…"
                          class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">{{ old('profile_notes', $user->profile_notes) }}</textarea>
                @error('profile_notes')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end border-t border-zinc-800 pt-6">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Enregistrer le profil
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const MAX_BYTES = 25 * 1024 * 1024;
    const form = document.getElementById('profile-settings-form');
    if (!form) return;
    const fileInput = form.querySelector('input[name="avatar"]');
    const errEl = document.getElementById('avatar-client-error');

    const showErr = (msg) => {
        if (!errEl) return;
        errEl.textContent = msg;
        errEl.classList.remove('hidden');
    };
    const clearErr = () => {
        if (!errEl) return;
        errEl.textContent = '';
        errEl.classList.add('hidden');
    };

    fileInput?.addEventListener('change', () => {
        clearErr();
        const f = fileInput.files?.[0];
        if (f && f.size > MAX_BYTES) {
            showErr('Ce fichier dépasse 25 Mo. Choisissez une image plus légère.');
            fileInput.value = '';
        }
    });

    form.addEventListener('submit', async (e) => {
        const f = fileInput?.files?.[0];
        if (!f || f.size === 0) {
            return;
        }
        if (f.size > MAX_BYTES) {
            e.preventDefault();
            showErr('Ce fichier dépasse 25 Mo.');
            return;
        }

        e.preventDefault();
        clearErr();

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                redirect: 'follow',
            });

            if (res.status === 413) {
                showErr('Fichier trop volumineux pour le serveur (limite 25 Mo).');
                return;
            }
            if (res.ok) {
                const html = await res.text();
                document.open();
                document.write(html);
                document.close();
                return;
            }
            showErr('Enregistrement impossible. Réessayez.');
        } catch {
            showErr('Erreur réseau. Réessayez.');
        }
    });
})();
</script>
@endpush
