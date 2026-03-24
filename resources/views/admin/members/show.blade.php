@extends('layouts.admin')
@section('title', $member->name)
@section('topbar_label', 'Membre')

@push('topbar_extra')
    @if ($member->trashed())
        <form method="POST" action="{{ route('admin.members.restore', $member) }}" class="inline">
            @csrf
            <button type="submit" class="flex items-center gap-2 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300 transition hover:bg-emerald-500/20">
                Restaurer le compte
            </button>
        </form>
    @else
        @if ($member->id !== auth()->id())
            <form method="POST" action="{{ route('admin.members.archive', $member) }}" class="inline" onsubmit="return confirm('Archiver ce compte ? La personne ne pourra plus se connecter.')">
                @csrf
                <button type="submit" class="flex items-center gap-2 rounded-lg border border-zinc-600 bg-zinc-900 px-3 py-2 text-xs font-semibold text-zinc-300 transition hover:border-amber-500/40 hover:text-amber-200">
                    Archiver
                </button>
            </form>
        @else
            <span class="text-[11px] text-zinc-500">Votre compte → Réglages / Compte pour vous archiver.</span>
        @endif
    @endif
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.members.index', ['status' => $member->trashed() ? 'archived' : 'active']) }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Membres</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">{{ $member->name }}</span>
    </div>

    @include('layouts.partials.flash')

    @if ($member->trashed())
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200 ring-1 ring-amber-500/20" role="status">
            Ce compte est <strong>archivé</strong> (connexion impossible). E-mail d’origine conservé pour référence : <span class="font-mono text-xs">{{ $member->archived_email ?? '—' }}</span>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Profil --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <div class="flex items-center gap-4">
                @include('partials.user-avatar', ['user' => $member, 'size' => 'h-14 w-14', 'textSize' => 'text-xl'])
                <div>
                    <p class="font-display text-lg font-bold text-white">{{ $member->name }}</p>
                    @if ($member->trashed() && $member->archived_email)
                        <p class="text-sm text-zinc-300">{{ $member->archived_email }}</p>
                        <p class="text-[11px] text-zinc-600">Identifiant technique : {{ $member->email }}</p>
                    @else
                        <p class="text-sm text-zinc-400">{{ $member->email }}</p>
                    @endif
                </div>
            </div>
            <dl class="mt-5 space-y-3 border-t border-zinc-800 pt-5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Inscrit le</dt>
                    <dd class="text-zinc-200">{{ $member->created_at->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">E-mail confirmé</dt>
                    <dd>
                        @if ($member->hasVerifiedEmail())
                            <span class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-300">Oui</span>
                        @else
                            <span class="rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-300">Non</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Admin système</dt>
                    <dd>
                        @if ($member->is_admin)
                            <span class="rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-300">Oui</span>
                        @else
                            <span class="text-zinc-500">Non</span>
                        @endif
                    </dd>
                </div>
                @if ($member->companies->isNotEmpty())
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Société(s)</dt>
                    <dd class="text-right text-zinc-300">
                        @foreach ($member->companies as $c)
                            <span class="block">
                                <a href="{{ route('admin.companies.show', $c) }}" class="hover:text-indigo-400">{{ $c->name }}</a>
                                @if ($member->hasRole('client') && $c->pivot->can_manage_company)
                                    <span class="ml-1 inline-flex rounded border border-amber-500/30 bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-amber-300">Responsable fiche</span>
                                @endif
                            </span>
                        @endforeach
                    </dd>
                </div>
                @endif
            </dl>
            @if (! $member->trashed() && ! $member->hasVerifiedEmail())
                <form method="POST" action="{{ route('admin.members.verify-email', $member) }}" class="mt-5 border-t border-zinc-800 pt-5" onsubmit="return confirm('Confirmer cette adresse e-mail manuellement ?');">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2.5 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/20">
                        Confirmer l’e-mail manuellement
                    </button>
                </form>
            @endif
            @if ($member->hasRole('student') && ! $member->trashed())
                <div class="mt-5 space-y-2 border-t border-zinc-800 pt-5">
                    <a href="{{ route('admin.student-courses.student', $member) }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-3 py-2.5 text-sm font-semibold text-indigo-300 transition hover:bg-indigo-500/20">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Cours de cet élève
                    </a>
                    <a href="{{ route('admin.student-subjects.student', $member) }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-violet-500/40 bg-violet-500/10 px-3 py-2.5 text-sm font-semibold text-violet-300 transition hover:bg-violet-500/20">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Matières &amp; dossiers
                    </a>
                </div>
            @endif
        </div>

        {{-- Rôles --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 lg:col-span-2">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Informations internes</h2>
            <p class="mt-1 text-xs text-zinc-500">Mise à jour de la fiche membre (nom, prénom, e-mail, téléphone, notes internes).</p>

            @if ($member->trashed())
                <p class="mt-5 text-sm text-zinc-500">Compte archivé — restaure le compte pour modifier ses informations.</p>
            @else
                <form method="POST" action="{{ route('admin.members.update', $member) }}" class="mt-5 space-y-4 border-b border-zinc-800 pb-6">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-500">Prénom</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $member->first_name) }}"
                                   class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @error('first_name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-500">Nom</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $member->last_name) }}"
                                   class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @error('last_name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-500">E-mail</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $member->email) }}"
                                   class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-500">Téléphone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $member->phone) }}"
                                   class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @error('phone')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="profile_notes" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-500">Notes internes</label>
                        <textarea id="profile_notes" name="profile_notes" rows="4"
                                  class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">{{ old('profile_notes', $member->profile_notes) }}</textarea>
                        @error('profile_notes')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                            Enregistrer les informations
                        </button>
                    </div>
                </form>
            @endif

            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Rôles &amp; accès</h2>
            <p class="mt-1 text-xs text-zinc-500">Les rôles définissent les portails accessibles par ce membre.</p>

            @if ($member->trashed())
                <p class="mt-5 text-sm text-zinc-500">Compte archivé — restaure le compte pour modifier les rôles.</p>
            @else
            <form method="POST" action="{{ route('admin.members.roles', $member) }}" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($allRoles as $role)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 transition hover:border-zinc-700 has-[:checked]:border-indigo-500/40 has-[:checked]:bg-indigo-500/8">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                @checked($member->roles->contains($role))
                                class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40"
                            >
                            <div>
                                <p class="text-sm font-medium text-zinc-100">{{ ucfirst($role->slug) }}</p>
                                <p class="text-[11px] text-zinc-500">Priorité {{ $role->priority }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3">
                    <input type="hidden" name="is_admin" value="0">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input
                            type="checkbox"
                            name="is_admin"
                            value="1"
                            @checked($member->is_admin)
                            class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-amber-500 focus:ring-amber-500/40"
                        >
                        <div>
                            <p class="text-sm font-medium text-amber-200">Admin système</p>
                            <p class="text-[11px] text-zinc-500">Accès à tous les portails + flag is_admin</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                        Enregistrer les rôles
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Collaborateurs — équipes</h2>
        <p class="mt-1 text-xs text-zinc-500">
            Affectation aux équipes du portail collaborateur. Le rôle <strong class="text-zinc-400">collaborator</strong> ou le statut admin est requis pour rester dans des équipes.
            Le <strong class="text-zinc-400">coordinateur collaborateurs</strong> peut nommer ou retirer les <strong class="text-zinc-400">gérants</strong> d’équipe (pas les membres ordinaires).
        </p>

        @if ($member->trashed())
            <p class="mt-4 text-sm text-zinc-500">Compte archivé — modification impossible.</p>
        @else
            @php
                $selectedTeamIds = old('collaborator_team_ids', $member->collaboratorTeams->pluck('id')->all());
                $selectedTeamIds = is_array($selectedTeamIds) ? array_map('intval', $selectedTeamIds) : [];
            @endphp
            <form method="POST" action="{{ route('admin.members.collaborator-access', $member) }}" class="mt-5 space-y-5">
                @csrf
                <input type="hidden" name="can_manage_collaborator_team_managers" value="0">
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 transition hover:border-zinc-700 has-[:checked]:border-violet-500/40">
                    <input type="checkbox" name="can_manage_collaborator_team_managers" value="1"
                           @checked(old('can_manage_collaborator_team_managers') !== null ? old('can_manage_collaborator_team_managers') === '1' : $member->can_manage_collaborator_team_managers)
                           class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-violet-500 focus:ring-violet-500/40">
                    <div>
                        <p class="text-sm font-medium text-zinc-100">Coordinateur collaborateurs</p>
                        <p class="text-[11px] text-zinc-500">Peut désigner les gérants sur les équipes (pas les permissions d’équipe).</p>
                    </div>
                </label>

                <fieldset class="space-y-2">
                    <legend class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Équipes</legend>
                    @if ($collaboratorTeams->isEmpty())
                        <p class="text-sm text-zinc-500">Aucune équipe en base — exécutez les migrations.</p>
                    @endif
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($collaboratorTeams as $t)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 transition hover:border-zinc-700 has-[:checked]:border-indigo-500/40">
                                <input type="checkbox" name="collaborator_team_ids[]" value="{{ $t->id }}"
                                       @checked(in_array($t->id, $selectedTeamIds, true))
                                       class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
                                <div>
                                    <p class="text-sm font-medium text-zinc-100">{{ $t->name }}</p>
                                    @if ($t->is_admin_team)
                                        <p class="text-[10px] font-semibold uppercase text-amber-400/90">Équipe administration</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                        Enregistrer équipes &amp; coordinateur
                    </button>
                </div>
            </form>
        @endif
    </div>

    @if ($member->id !== auth()->id())
        <div class="rounded-2xl border border-red-900/40 bg-red-950/20 p-6 ring-1 ring-red-500/10">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-red-300">Suppression définitive (RGPD)</h2>
            <p class="mt-1 text-xs text-zinc-500">
                Efface la ligne utilisateur et les données associées (cours, matières, pivots, etc.). Irréversible.
                @if ($member->trashed())
                    Vous pouvez aussi laisser le compte archivé sans le détruire.
                @endif
            </p>
            <form method="POST" action="{{ route('admin.members.force-destroy', $member) }}" class="mt-4 flex flex-wrap items-end gap-3" onsubmit="return confirm('Supprimer DÉFINITIVEMENT ce compte et toutes ses données ?')">
                @csrf
                @method('DELETE')
                <div class="w-full flex-1 sm:min-w-[12rem]">
                    <label for="confirmation" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tape <span class="font-mono text-zinc-400">SUPPRIMER</span></label>
                    <input type="text" id="confirmation" name="confirmation" autocomplete="off" placeholder="SUPPRIMER"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-red-500/50 focus:outline-none focus:ring-2 focus:ring-red-500/20">
                    @error('confirmation')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
                    Supprimer définitivement
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
