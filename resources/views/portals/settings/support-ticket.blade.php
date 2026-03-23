@extends('layouts.admin')

@section('title', 'Réglages — Nouvelle demande')
@section('topbar_label', 'Nouvelle demande')

@section('content')
    <div class="w-full min-w-0 space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Support</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Envoyer une demande</h1>
            <p class="text-sm text-zinc-400">
                Pour vous personnellement ou pour l’une de vos sociétés. Choisissez le type de demande et décrivez-la ; nous vous répondrons à l’adresse e-mail de votre compte.
            </p>
        </header>

        @include('layouts.partials.flash')

        <form method="POST" action="{{ route('portals.settings.support-ticket.store') }}" class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            @csrf

            <div>
                <span class="mb-2 block text-sm font-medium text-zinc-300">Portée</span>
                <p class="mb-3 text-xs text-zinc-500">La demande concerne votre compte personnel ou une société à laquelle vous êtes rattaché.</p>
                <div class="space-y-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-700/80 bg-zinc-950/40 p-3 ring-1 ring-transparent has-[:checked]:border-indigo-500/40 has-[:checked]:ring-indigo-500/20">
                        <input type="radio" name="company_id" value="" @checked(old('company_id', '') === '' || old('company_id') === null) class="mt-1 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
                        <span>
                            <span class="block text-sm font-medium text-zinc-200">Compte personnel</span>
                            <span class="text-xs text-zinc-500">À titre individuel, sans rattachement société.</span>
                        </span>
                    </label>
                    @foreach ($companies as $company)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-700/80 bg-zinc-950/40 p-3 ring-1 ring-transparent has-[:checked]:border-indigo-500/40 has-[:checked]:ring-indigo-500/20">
                            <input type="radio" name="company_id" value="{{ $company->id }}" @checked((string) old('company_id') === (string) $company->id) class="mt-1 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
                            <span>
                                <span class="block text-sm font-medium text-zinc-200">{{ $company->name }}</span>
                                <span class="text-xs text-zinc-500">Demande au nom de cette société.</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @if ($companies->isEmpty())
                    <p class="mt-2 text-xs text-zinc-600">Aucune société liée — seul le mode « compte personnel » est disponible. Vous pouvez associer des sociétés depuis le portail « Mes sociétés » si vous y avez accès.</p>
                @endif
                @error('company_id')<p class="mt-2 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category" class="mb-1.5 block text-sm font-medium text-zinc-300">Type de demande</label>
                <select id="category" name="category" required class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @foreach ($categoryChoices as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="subject" class="mb-1.5 block text-sm font-medium text-zinc-300">Sujet</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255" autocomplete="off"
                       class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30" placeholder="Résumé en une ligne">
                @error('subject')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="body" class="mb-1.5 block text-sm font-medium text-zinc-300">Message</label>
                <textarea id="body" name="body" rows="8" required class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30" placeholder="Décrivez votre demande avec le plus de précisions possible.">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                    Envoyer la demande
                </button>
                <a href="{{ route('portals.settings') }}" class="text-sm font-medium text-zinc-400 hover:text-zinc-200">Annuler</a>
            </div>
        </form>
    </div>
@endsection
