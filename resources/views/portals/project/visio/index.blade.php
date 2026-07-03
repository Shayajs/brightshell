@extends('layouts.admin')

@section('title', 'Visio — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Visioconférences</h1>
        <p class="mt-1 text-sm text-zinc-500">Sessions LiveKit liées au projet, invitations invitées sans compte, partage doc + devis en direct.</p>
    </header>

    @can('update', $project)
        <form method="POST" action="{{ route('portals.project.visio.store', $project) }}" class="grid gap-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 sm:grid-cols-3">
            @csrf
            <div class="sm:col-span-2">
                <label class="text-xs text-zinc-500">Titre de la salle</label>
                <input type="text" name="title" required value="{{ old('title', 'Session visio — '.$project->name) }}" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-xs text-zinc-500">Début (optionnel)</label>
                <input type="datetime-local" name="starts_at" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
            </div>
            <div class="sm:col-span-3">
                <button type="submit" class="rounded-lg bg-fuchsia-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-fuchsia-500">
                    Créer une salle
                </button>
            </div>
        </form>
    @endcan

    <section class="space-y-4">
        @forelse ($rooms as $room)
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-lg font-bold text-white">{{ $room->title }}</h2>
                        <p class="mt-1 text-xs text-zinc-500">
                            Salle:
                            <a class="text-fuchsia-300 hover:text-fuchsia-200" href="{{ route('visio.room.show', $room) }}" target="_blank" rel="noopener">
                                {{ route('visio.room.show', $room) }}
                            </a>
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">Statut: {{ $room->status }} · Invitations: {{ $room->invitations->count() }} · Participants: {{ $room->participants->count() }}</p>
                    </div>
                    @can('update', $project)
                        <form method="POST" action="{{ route('portals.project.visio.destroy', [$project, $room]) }}" onsubmit="return confirm('Supprimer cette salle visio ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-300">Supprimer</button>
                        </form>
                    @endcan
                </div>

                @can('update', $project)
                    <form method="POST" action="{{ route('portals.project.visio.invite', [$project, $room]) }}" class="mt-4 flex flex-col gap-3 rounded-xl border border-zinc-800/80 bg-zinc-950/40 p-4 sm:flex-row sm:items-end">
                        @csrf
                        <div class="w-full sm:flex-1">
                            <label class="text-xs text-zinc-500">E-mail invité (optionnel)</label>
                            <input type="email" name="email" placeholder="invite@example.com" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500">Expire dans (jours)</label>
                            <input type="number" name="expires_in_days" min="1" max="30" value="7" class="mt-1 w-28 rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                        </div>
                        <label class="flex items-center gap-2 text-xs text-zinc-400">
                            <input type="checkbox" name="can_present" value="1" class="rounded border-zinc-600 bg-zinc-900 text-fuchsia-500">
                            Peut présenter
                        </label>
                        <button type="submit" class="rounded-lg border border-fuchsia-500/40 bg-fuchsia-600/20 px-4 py-2 text-sm font-semibold text-fuchsia-200 hover:bg-fuchsia-600/30">
                            Créer invitation
                        </button>
                    </form>
                @endcan

                @if ($room->invitations->isNotEmpty())
                    <div class="mt-4 grid gap-2">
                        @foreach ($room->invitations->take(10) as $invitation)
                            <div class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-xs text-zinc-400">
                                <span class="text-zinc-200">{{ $invitation->email ?: 'Lien invité anonyme' }}</span>
                                · {{ route('visio.join.show', $invitation->token) }}
                                @if ($invitation->accepted_at)
                                    · <span class="text-emerald-300">acceptée</span>
                                @elseif($invitation->isExpired())
                                    · <span class="text-red-300">expirée</span>
                                @else
                                    · <span class="text-fuchsia-300">active</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900/40 p-8 text-center text-sm text-zinc-500">
                Aucune salle visio pour ce projet.
            </div>
        @endforelse
    </section>

    {{ $rooms->links() }}
</div>
@endsection
