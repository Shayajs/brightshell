@extends('layouts.admin')

@section('title', 'Permissions — '.$team->name)
@section('topbar_label', 'Portail collaborateurs')
@section('portal_main_max', 'max-w-none')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center gap-3 text-sm">
            <a href="{{ route('portals.collabs.teams.show', $team) }}" class="text-zinc-500 hover:text-indigo-400">← {{ $team->name }}</a>
        </div>

        @include('layouts.partials.flash')

        <header>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white">Permissions — {{ $team->name }}</h1>
            @if ($team->is_admin_team && ! auth()->user()?->isAdmin())
                <p class="mt-2 text-sm text-amber-400/90">Vous pouvez consulter les droits de l’équipe administration, mais seul un admin système peut les modifier.</p>
            @elseif (! $canEdit)
                <p class="mt-2 text-sm text-zinc-500">Lecture seule — seuls les membres de l’équipe d’administration collaborateurs (ou un admin) peuvent modifier les autres équipes.</p>
            @endif
        </header>

        @if ($canEdit)
            <form method="POST" action="{{ route('portals.collabs.teams.permissions.update', $team) }}" class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                @csrf
                @method('PUT')
                <fieldset class="space-y-3">
                    <legend class="text-sm font-semibold text-zinc-200">Capabilities</legend>
                    @foreach ($allCapabilities as $cap)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 transition hover:border-zinc-700 has-[:checked]:border-indigo-500/40">
                            <input type="checkbox" name="capabilities[]" value="{{ $cap->id }}"
                                   @checked($team->capabilities->contains($cap))
                                   class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
                            <div>
                                <p class="text-sm font-medium text-zinc-100">{{ $cap->label }}</p>
                                @if ($cap->description)
                                    <p class="text-[11px] text-zinc-500">{{ $cap->description }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </fieldset>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Enregistrer</button>
                </div>
            </form>
        @else
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <ul class="space-y-2">
                    @forelse ($team->capabilities as $cap)
                        <li class="flex items-center justify-between rounded-lg border border-zinc-800/80 bg-zinc-950/40 px-4 py-3">
                            <span class="text-sm text-zinc-200">{{ $cap->label }}</span>
                            <span class="text-[11px] text-zinc-500">{{ $cap->slug }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-zinc-500">Aucune capability.</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>
@endsection
