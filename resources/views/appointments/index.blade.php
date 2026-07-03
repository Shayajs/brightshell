@use('App\Support\BrightshellDomain')

@extends('layouts.auth')

@section('title', 'Prendre rendez-vous')

@section('content')
    <a href="{{ BrightshellDomain::publicSiteUrl() }}" class="auth-back-link">← Retour au site</a>

    @include('layouts.partials.flash')

    <h1 class="auth-title">Prendre rendez-vous</h1>
    <p class="auth-subtitle">Choisissez un créneau disponible et laissez vos coordonnées. Je vous confirme le rendez-vous rapidement.</p>

    @if ($slots->isEmpty())
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-900/40 px-4 py-6 text-center text-sm text-zinc-400">
            Aucun créneau disponible pour le moment.
            <a href="{{ route('contact') }}" class="mt-2 block text-indigo-400 hover:text-indigo-300">Me contacter directement →</a>
        </div>
    @else
        <form method="post" action="{{ route('appointments.store') }}" class="auth-form" novalidate>
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

            <div>
                <label for="appointment_slot_id" class="auth-label">Créneau disponible</label>
                <select id="appointment_slot_id" name="appointment_slot_id" required class="auth-input">
                    <option value="">— Choisir un créneau —</option>
                    @foreach ($slots as $slot)
                        <option value="{{ $slot->id }}" @selected(old('appointment_slot_id') == $slot->id)>
                            {{ $slot->formattedRange() }}
                        </option>
                    @endforeach
                </select>
                @error('appointment_slot_id') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="auth-label">Prénom</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" class="auth-input">
                    @error('first_name') <p class="auth-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="auth-label">Nom</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" class="auth-input">
                    @error('last_name') <p class="auth-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="email" class="auth-label">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="auth-input">
                @error('email') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="auth-label">Téléphone <span class="text-zinc-500">(optionnel)</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="auth-input">
                @error('phone') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="auth-label">Message <span class="text-zinc-500">(optionnel)</span></label>
                <textarea id="message" name="message" rows="4" maxlength="2000" placeholder="Objet du rendez-vous, contexte…" class="auth-input contact-textarea">{{ old('message') }}</textarea>
                @error('message') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="auth-submit">Demander ce créneau</button>
        </form>
    @endif

    <p class="mt-6 text-center text-xs text-zinc-500">
        Vous préférez écrire ?
        <a href="{{ route('contact') }}" class="text-indigo-400 hover:text-indigo-300">Formulaire de contact</a>
    </p>
@endsection
