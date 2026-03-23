@extends('layouts.admin')
@section('title', 'Mon entreprise')
@section('topbar_label', 'Déclarations')

@php
    use App\Enums\LegalStatus;
@endphp

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-violet-400/90">Paramètres</p>
        <h1 class="font-display text-2xl font-bold text-white">Mon entreprise</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-500">
            Ces infos sont <strong class="text-zinc-400">internes</strong> sauf ce que vous exposez via l’API publique (voir cases à cocher). Les <strong class="text-zinc-400">sociétés</strong> listées ailleurs dans l’admin sont vos <em>clients</em>, pas votre statut juridique.
        </p>
    </div>

    @include('admin.declarations._nav')

    @include('layouts.partials.flash')

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.declarations.business.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Identité</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'legal_name', 'label' => 'Dénomination / nom légal', 'value' => old('legal_name', $profile->legal_name), 'placeholder' => 'Ex. DUPONT Jean — Micro-entreprise'])
                    </div>
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'trade_name', 'label' => 'Nom commercial (affiché si renseigné)', 'value' => old('trade_name', $profile->trade_name)])
                    </div>
                    <div class="sm:col-span-2">
                        <label for="legal_status" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Statut juridique</label>
                        <select id="legal_status" name="legal_status"
                                class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/25">
                            @foreach (LegalStatus::cases() as $case)
                                <option value="{{ $case->value }}" @selected(old('legal_status', $profile->legal_status) === $case->value)>{{ $case->label() }}</option>
                            @endforeach
                        </select>
                        @error('legal_status')
                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    @include('layouts.partials.form-field', ['name' => 'siret', 'label' => 'SIRET', 'value' => old('siret', $profile->siret), 'placeholder' => '14 chiffres'])
                    @include('layouts.partials.form-field', ['name' => 'ape_code', 'label' => 'Code APE / NAF', 'value' => old('ape_code', $profile->ape_code)])
                </div>
            </div>

            <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">TVA (évolution)</h2>
                <p class="text-xs text-zinc-500">Aujourd’hui en auto-entreprise sans TVA : laissez décoché. Cochez si vous devenez assujetti (ex. après passage en EI).</p>
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 has-[:checked]:border-violet-500/40">
                    <input type="checkbox" name="vat_registered" value="1" class="mt-1 h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-violet-500"
                           @checked(old('vat_registered', $profile->vat_registered))>
                    <span class="text-sm text-zinc-300">Assujetti à la TVA</span>
                </label>
                @include('layouts.partials.form-field', ['name' => 'vat_number', 'label' => 'Numéro de TVA intracommunautaire', 'value' => old('vat_number', $profile->vat_number)])
            </div>

            <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Adresse</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'street_line1', 'label' => 'Ligne 1', 'value' => old('street_line1', $profile->street_line1)])
                    </div>
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'street_line2', 'label' => 'Ligne 2', 'value' => old('street_line2', $profile->street_line2)])
                    </div>
                    @include('layouts.partials.form-field', ['name' => 'postal_code', 'label' => 'Code postal', 'value' => old('postal_code', $profile->postal_code)])
                    @include('layouts.partials.form-field', ['name' => 'city', 'label' => 'Ville', 'value' => old('city', $profile->city)])
                    @include('layouts.partials.form-field', ['name' => 'country', 'label' => 'Pays', 'value' => old('country', $profile->country)])
                </div>
            </div>

            <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Contact public &amp; activité</h2>
                <p class="text-xs text-zinc-500">Champs pouvant être partagés via l’API (email / tél. / site / description d’activité).</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    @include('layouts.partials.form-field', ['name' => 'public_email', 'label' => 'E-mail public', 'type' => 'email', 'value' => old('public_email', $profile->public_email)])
                    @include('layouts.partials.form-field', ['name' => 'public_phone', 'label' => 'Téléphone public', 'value' => old('public_phone', $profile->public_phone)])
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'website_url', 'label' => 'Site web', 'type' => 'text', 'value' => old('website_url', $profile->website_url), 'placeholder' => 'https://…'])
                    </div>
                </div>
                <div>
                    <label for="activity_description" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Description de l’activité (API)</label>
                    <textarea id="activity_description" name="activity_description" rows="4"
                              class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/25"
                    >{{ old('activity_description', $profile->activity_description) }}</textarea>
                    @error('activity_description')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">API publique — champs sensibles</h2>
                <p class="text-xs text-zinc-500">Par défaut l’API ne renvoie <strong class="text-zinc-400">pas</strong> l’adresse complète ni le SIRET. Cochez seulement si vous voulez les exposer.</p>
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 has-[:checked]:border-amber-500/40">
                    <input type="checkbox" name="publish_street_on_api" value="1" class="mt-1 h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-amber-500"
                           @checked(old('publish_street_on_api', $profile->publish_street_on_api))>
                    <span class="text-sm text-zinc-300">Publier les lignes d’adresse (rue) dans l’API</span>
                </label>
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 has-[:checked]:border-amber-500/40">
                    <input type="checkbox" name="publish_siret_on_api" value="1" class="mt-1 h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-amber-500"
                           @checked(old('publish_siret_on_api', $profile->publish_siret_on_api))>
                    <span class="text-sm text-zinc-300">Publier le SIRET dans l’API</span>
                </label>
                @if ($apiBaseUrl)
                    <p class="text-xs text-zinc-600">Endpoint : <code class="rounded bg-zinc-950 px-1 py-0.5 text-sky-400">{{ $apiBaseUrl }}</code></p>
                @endif
            </div>

            <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Notes internes</h2>
                <p class="text-xs text-zinc-500">Jamais envoyées sur l’API.</p>
                <textarea name="internal_notes" rows="4"
                          class="w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/25"
                >{{ old('internal_notes', $profile->internal_notes) }}</textarea>
                @error('internal_notes')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:bg-violet-500">Enregistrer</button>
                <a href="{{ route('admin.declarations.index') }}" class="rounded-xl border border-zinc-700 px-5 py-2.5 text-sm font-semibold text-zinc-400 transition hover:border-zinc-600 hover:text-zinc-200">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
