@extends('layouts.admin')
@section('title', 'CV — Identité & Contact')
@section('topbar_label', 'CV — Contact')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.cv.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Mon CV</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">Identité & Contact</span>
    </div>

    @include('layouts.partials.flash')

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.cv.contact.update') }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">État civil</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @include('layouts.partials.form-field', ['name' => 'etat_civil[prenom]', 'label' => 'Prénom', 'type' => 'text', 'value' => old('etat_civil.prenom', $contact['etat_civil']['prenom'] ?? '')])
                    @include('layouts.partials.form-field', ['name' => 'etat_civil[nom]', 'label' => 'Nom', 'type' => 'text', 'value' => old('etat_civil.nom', $contact['etat_civil']['nom'] ?? '')])
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'etat_civil[titre]', 'label' => 'Titre professionnel', 'type' => 'text', 'value' => old('etat_civil.titre', $contact['etat_civil']['titre'] ?? ''), 'placeholder' => 'Développeur Full Stack'])
                    </div>
                    @include('layouts.partials.form-field', ['name' => 'etat_civil[email]', 'label' => 'E-mail', 'type' => 'email', 'value' => old('etat_civil.email', $contact['etat_civil']['email'] ?? '')])
                    @include('layouts.partials.form-field', ['name' => 'etat_civil[telephone]', 'label' => 'Téléphone', 'type' => 'text', 'value' => old('etat_civil.telephone', $contact['etat_civil']['telephone'] ?? '')])
                    @include('layouts.partials.form-field', ['name' => 'etat_civil[site_web]', 'label' => 'Site web', 'type' => 'url', 'value' => old('etat_civil.site_web', $contact['etat_civil']['site_web'] ?? '')])
                    @include('layouts.partials.form-field', ['name' => 'etat_civil[localisation]', 'label' => 'Localisation', 'type' => 'text', 'value' => old('etat_civil.localisation', $contact['etat_civil']['localisation'] ?? '')])
                    @include('layouts.partials.form-field', ['name' => 'etat_civil[permis]', 'label' => 'Permis', 'type' => 'text', 'value' => old('etat_civil.permis', $contact['etat_civil']['permis'] ?? ''), 'placeholder' => 'Permis B'])
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Réseaux sociaux</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    @include('layouts.partials.form-field', ['name' => 'reseaux_sociaux[github]', 'label' => 'GitHub', 'type' => 'text', 'value' => old('reseaux_sociaux.github', $contact['reseaux_sociaux']['github'] ?? ''), 'placeholder' => '@Shayajs'])
                    @include('layouts.partials.form-field', ['name' => 'reseaux_sociaux[linkedin]', 'label' => 'LinkedIn', 'type' => 'text', 'value' => old('reseaux_sociaux.linkedin', $contact['reseaux_sociaux']['linkedin'] ?? ''), 'placeholder' => '@lucas-espinar'])
                    @include('layouts.partials.form-field', ['name' => 'reseaux_sociaux[twitter_x]', 'label' => 'X / Twitter', 'type' => 'text', 'value' => old('reseaux_sociaux.twitter_x', $contact['reseaux_sociaux']['twitter_x'] ?? ''), 'placeholder' => '@lucas_shaya'])
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-3">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Résumé profil</h2>
                <p class="text-xs text-zinc-500">Affiché en tête de CV.</p>
                <textarea
                    name="resume_profil"
                    rows="4"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                >{{ old('resume_profil', $contact['resume_profil'] ?? '') }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.cv.index') }}" class="rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-medium text-zinc-400 transition hover:text-zinc-200">Annuler</a>
                <button type="submit"
                    class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
