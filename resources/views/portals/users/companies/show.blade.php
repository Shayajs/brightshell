@extends('layouts.admin')

@section('title', $company->name)
@section('topbar_label', $company->name)

@section('content')
    <div class="space-y-8">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('portals.users.companies.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Mes sociétés</a>
        </div>

        @include('layouts.partials.flash')

        <header class="flex flex-wrap items-start gap-4">
            @if ($company->logoUrl())
                <img src="{{ $company->logoUrl() }}" alt="" class="h-16 w-16 shrink-0 rounded-xl border border-zinc-800 object-contain bg-zinc-950 p-1">
            @else
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-zinc-800 bg-zinc-950 text-xl font-bold text-zinc-600 font-display" aria-hidden="true">
                    {{ strtoupper(substr($company->name, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0 flex-1 space-y-1">
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $company->name }}</h1>
                @if ($canManage)
                    <p class="text-sm text-amber-200/90">Vous <strong>pouvez modifier</strong> cette société (logo et informations).</p>
                @else
                    <p class="text-sm text-zinc-500">Vous consultez cette fiche en <strong class="text-zinc-400">lecture seule</strong>. Les documents et données sensibles sont visibles sans pouvoir les modifier.</p>
                @endif
            </div>
        </header>

        @if ($canManage)
            <form
                method="POST"
                action="{{ route('portals.users.companies.update', $company) }}"
                enctype="multipart/form-data"
                class="space-y-6 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Logo de la société</label>
                    <input type="file" name="logo" accept="image/*"
                           class="block w-full text-sm text-zinc-400 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-800 file:px-3 file:py-2 file:text-sm file:font-medium file:text-zinc-200 hover:file:bg-zinc-700">
                    @error('logo')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    @if ($company->logoUrl())
                        <label class="mt-3 flex cursor-pointer items-center gap-2 text-xs text-zinc-500">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-zinc-600 bg-zinc-950 text-indigo-500">
                            Supprimer le logo actuel
                        </label>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="mb-1.5 block text-sm font-medium text-zinc-300">Nom</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $company->name) }}" required
                               class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    @include('layouts.partials.form-field', ['name' => 'siret', 'label' => 'SIRET', 'type' => 'text', 'value' => old('siret', $company->siret), 'placeholder' => '14 chiffres'])
                    @include('layouts.partials.form-field', ['name' => 'website', 'label' => 'Site web', 'type' => 'url', 'value' => old('website', $company->website), 'placeholder' => 'https://…'])
                    @include('layouts.partials.form-field', ['name' => 'address', 'label' => 'Adresse', 'type' => 'text', 'value' => old('address', $company->address)])
                    @include('layouts.partials.form-field', ['name' => 'city', 'label' => 'Ville', 'type' => 'text', 'value' => old('city', $company->city)])
                    @include('layouts.partials.form-field', ['name' => 'country', 'label' => 'Pays (code ISO)', 'type' => 'text', 'value' => old('country', $company->country), 'placeholder' => 'FR'])
                    @include('layouts.partials.form-field', ['name' => 'contact_name', 'label' => 'Contact', 'type' => 'text', 'value' => old('contact_name', $company->contact_name)])
                    @include('layouts.partials.form-field', ['name' => 'contact_email', 'label' => 'E-mail contact', 'type' => 'email', 'value' => old('contact_email', $company->contact_email)])
                </div>

                <div>
                    <label for="notes" class="mb-1.5 block text-sm font-medium text-zinc-300">Notes</label>
                    <textarea id="notes" name="notes" rows="4"
                              class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">{{ old('notes', $company->notes) }}</textarea>
                    @error('notes')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end border-t border-zinc-800 pt-6">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                        Enregistrer
                    </button>
                </div>
            </form>
        @else
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 lg:col-span-1">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Coordonnées</h2>
                    <dl class="space-y-3 text-sm">
                        @if ($company->siret)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">SIRET</dt>
                                <dd class="font-mono text-zinc-200">{{ $company->siret }}</dd>
                            </div>
                        @endif
                        @if ($company->address || $company->city)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">Adresse</dt>
                                <dd class="text-right text-zinc-300">{{ implode(', ', array_filter([$company->address, $company->city, $company->country])) }}</dd>
                            </div>
                        @endif
                        @if ($company->website)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">Site</dt>
                                <dd><a href="{{ $company->website }}" target="_blank" class="text-indigo-400 hover:underline">{{ parse_url($company->website, PHP_URL_HOST) }}</a></dd>
                            </div>
                        @endif
                        @if ($company->contact_name)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">Contact</dt>
                                <dd class="text-zinc-300">{{ $company->contact_name }}</dd>
                            </div>
                        @endif
                        @if ($company->contact_email)
                            <div class="flex justify-between gap-4">
                                <dt class="text-zinc-500">E-mail</dt>
                                <dd><a href="mailto:{{ $company->contact_email }}" class="text-indigo-400 hover:underline">{{ $company->contact_email }}</a></dd>
                            </div>
                        @endif
                    </dl>
                    @if ($company->notes)
                        <div class="rounded-lg border border-zinc-800 bg-zinc-950/50 p-3 text-sm text-zinc-400">{{ $company->notes }}</div>
                    @endif
                </div>

                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5 lg:col-span-2">
                    <div class="border-b border-zinc-800 px-5 py-4">
                        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Factures</h2>
                        <p class="mt-1 text-xs text-zinc-500">Lecture seule — détail géré avec BrightShell.</p>
                    </div>
                    @if ($company->invoices->isEmpty())
                        <p class="px-5 py-8 text-center text-sm text-zinc-600">Aucune facture pour cette société.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-800 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                                        <th class="px-5 py-3">N°</th>
                                        <th class="px-5 py-3">Objet</th>
                                        <th class="px-5 py-3">Montant HT</th>
                                        <th class="px-5 py-3">Statut</th>
                                        <th class="px-5 py-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/60">
                                    @foreach ($company->invoices as $inv)
                                        @php
                                            $badge = match ($inv->status) {
                                                'paid' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                                                'sent' => 'border-indigo-500/30 bg-indigo-500/10 text-indigo-300',
                                                'cancelled' => 'border-red-500/30 bg-red-500/10 text-red-300',
                                                default => 'border-zinc-600/30 bg-zinc-800/50 text-zinc-400',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="px-5 py-3 font-mono text-xs text-zinc-400">{{ $inv->number }}</td>
                                            <td class="px-5 py-3 text-zinc-300">{{ $inv->label ?: '—' }}</td>
                                            <td class="px-5 py-3 text-zinc-200">{{ number_format((float) $inv->amount_ht, 2, ',', ' ') }} €</td>
                                            <td class="px-5 py-3">
                                                <span class="inline-flex rounded-md border px-2 py-0.5 text-[11px] font-semibold {{ $badge }}">{{ $inv->statusLabel() }}</span>
                                            </td>
                                            <td class="px-5 py-3 text-zinc-500">{{ $inv->issued_at?->format('d/m/Y') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <section class="rounded-2xl border border-zinc-800 bg-zinc-950/40 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-zinc-300">Documents</h2>
                <p class="mt-2 text-sm text-zinc-500">
                    Les documents contractuels et livrables seront listés ici en <strong class="font-medium text-zinc-400">lecture seule</strong> pour tous les membres rattachés à la société.
                    Seuls les responsables désignés pourront agir sur la fiche entreprise (informations et logo).
                </p>
            </section>
        @endif

        @if ($canManage)
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
                <div class="border-b border-zinc-800 px-5 py-4">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Factures</h2>
                    <p class="mt-1 text-xs text-zinc-500">Lecture seule — suivi des émissions BrightShell.</p>
                </div>
                @if ($company->invoices->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-zinc-600">Aucune facture pour cette société.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-zinc-800 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                                    <th class="px-5 py-3">N°</th>
                                    <th class="px-5 py-3">Objet</th>
                                    <th class="px-5 py-3">Montant HT</th>
                                    <th class="px-5 py-3">Statut</th>
                                    <th class="px-5 py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60">
                                @foreach ($company->invoices as $inv)
                                    @php
                                        $badge = match ($inv->status) {
                                            'paid' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                                            'sent' => 'border-indigo-500/30 bg-indigo-500/10 text-indigo-300',
                                            'cancelled' => 'border-red-500/30 bg-red-500/10 text-red-300',
                                            default => 'border-zinc-600/30 bg-zinc-800/50 text-zinc-400',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-3 font-mono text-xs text-zinc-400">{{ $inv->number }}</td>
                                        <td class="px-5 py-3 text-zinc-300">{{ $inv->label ?: '—' }}</td>
                                        <td class="px-5 py-3 text-zinc-200">{{ number_format((float) $inv->amount_ht, 2, ',', ' ') }} €</td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[11px] font-semibold {{ $badge }}">{{ $inv->statusLabel() }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-zinc-500">{{ $inv->issued_at?->format('d/m/Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <section class="rounded-2xl border border-zinc-800 bg-zinc-950/40 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-zinc-300">Documents</h2>
                <p class="mt-2 text-sm text-zinc-500">
                    Espace réservé aux futurs dépôts (devis, contrats, livrables). Les autres membres de la société y auront accès en lecture seule ;
                    vous pouvez déjà mettre à jour les informations et le logo ci-dessus.
                </p>
            </section>
        @endif
    </div>
@endsection
