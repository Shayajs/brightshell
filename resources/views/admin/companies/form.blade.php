@extends('layouts.admin')
@section('title', $company ? 'Modifier '.$company->name : 'Nouvelle société')
@section('topbar_label', $company ? 'Modifier la société' : 'Nouvelle société')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.companies.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Sociétés</a>
        @if ($company)
            <span class="text-zinc-700">/</span>
            <a href="{{ route('admin.companies.show', $company) }}" class="text-sm text-zinc-500 hover:text-indigo-400">{{ $company->name }}</a>
            <span class="text-zinc-700">/</span>
            <span class="text-sm text-zinc-300">Modifier</span>
        @endif
    </div>

    @include('layouts.partials.flash')

    <div class="mx-auto max-w-2xl">
        <form
            method="POST"
            action="{{ $company ? route('admin.companies.update', $company) : route('admin.companies.store') }}"
            class="space-y-6"
        >
            @csrf
            @if ($company) @method('PUT') @endif

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Informations</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        @include('layouts.partials.form-field', ['name' => 'name', 'label' => 'Nom *', 'type' => 'text', 'value' => old('name', $company?->name), 'required' => true])
                    </div>
                    @include('layouts.partials.form-field', ['name' => 'siret', 'label' => 'SIRET', 'type' => 'text', 'value' => old('siret', $company?->siret), 'placeholder' => '14 chiffres'])
                    @include('layouts.partials.form-field', ['name' => 'website', 'label' => 'Site web', 'type' => 'url', 'value' => old('website', $company?->website), 'placeholder' => 'https://…'])
                    @include('layouts.partials.form-field', ['name' => 'address', 'label' => 'Adresse', 'type' => 'text', 'value' => old('address', $company?->address)])
                    @include('layouts.partials.form-field', ['name' => 'city', 'label' => 'Ville', 'type' => 'text', 'value' => old('city', $company?->city)])
                    @include('layouts.partials.form-field', ['name' => 'contact_name', 'label' => 'Contact', 'type' => 'text', 'value' => old('contact_name', $company?->contact_name)])
                    @include('layouts.partials.form-field', ['name' => 'contact_email', 'label' => 'E-mail contact', 'type' => 'email', 'value' => old('contact_email', $company?->contact_email)])
                </div>

                <div>
                    <label for="notes" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
                    >{{ old('notes', $company?->notes) }}</textarea>
                </div>
            </div>

            {{-- Membres liés --}}
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 space-y-4">
                <div>
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Membres liés</h2>
                    <p class="mt-1 text-xs text-zinc-500">Optionnel — sert uniquement aux statistiques.</p>
                </div>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($members as $m)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-950/50 px-3 py-2.5 transition hover:border-zinc-700 has-[:checked]:border-indigo-500/30 has-[:checked]:bg-indigo-500/8">
                            <input
                                type="checkbox"
                                name="user_ids[]"
                                value="{{ $m->id }}"
                                @checked($company && $company->users->contains($m))
                                class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40"
                            >
                            <span class="text-sm text-zinc-300">{{ $m->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.companies.index') }}"
                    class="rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-medium text-zinc-400 transition hover:text-zinc-200">
                    Annuler
                </a>
                <button type="submit"
                    class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                    {{ $company ? 'Enregistrer' : 'Créer la société' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
