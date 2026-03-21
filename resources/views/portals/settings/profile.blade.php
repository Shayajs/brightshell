@extends('layouts.admin')

@section('title', 'Réglages — Profil')
@section('topbar_label', 'Réglages')

@section('content')
    @include('portals.settings.partials.subnav', ['current' => 'profile'])

    <div class="mx-auto max-w-2xl space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Identité</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Profil</h1>
            <p class="text-sm text-zinc-400">Nom, e-mail, téléphone et notes personnelles (visibles uniquement par toi et l’équipe habilitée).</p>
        </header>

        @include('layouts.partials.flash')

        <form method="POST" action="{{ route('portals.settings.profile.update') }}" class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-zinc-300">Nom complet</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
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
