@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Demandes — '.$project->name)
@section('topbar_label', $project->name)

@section('content')
<div class="space-y-8">
    <p class="text-sm text-zinc-500">
        <a href="{{ route('portals.project.show', $project) }}" class="text-cyan-400/90 hover:text-cyan-300">← Projet</a>
    </p>
    @include('portals.project.partials.subnav', ['project' => $project])
    @include('layouts.partials.flash')

    <header>
        <h1 class="font-display text-2xl font-bold text-white">Demandes</h1>
        <p class="mt-1 text-sm text-zinc-500">Lien vers un ticket support : réservé au droit <strong class="text-zinc-400">modifier</strong>.</p>
    </header>

    <form method="POST" action="{{ route('portals.project.requests.store', $project) }}" class="space-y-3 rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
        @csrf
        <h2 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Nouvelle demande</h2>
        <input type="text" name="title" required placeholder="Titre" value="{{ old('title') }}" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
        <textarea name="body" rows="3" placeholder="Détail" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">{{ old('body') }}</textarea>
        @can('update', $project)
            <div>
                <label class="text-xs text-zinc-500">Lier un ticket support (optionnel)</label>
                <select name="support_ticket_id" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white">
                    <option value="">—</option>
                    @foreach ($supportTickets as $t)
                        <option value="{{ $t->id }}" @selected((int) old('support_ticket_id') === $t->id)>#{{ $t->id }} — {{ Str::limit($t->subject, 60) }}</option>
                    @endforeach
                </select>
            </div>
        @endcan
        <button type="submit" class="rounded-lg bg-cyan-600/90 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Créer</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-800 text-[10px] font-semibold uppercase text-zinc-500">
                <tr>
                    <th class="px-4 py-3">Demande</th>
                    <th class="px-4 py-3">Auteur</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Support</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/60">
                @forelse ($requests as $r)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-zinc-100">{{ $r->title }}</p>
                            @if ($r->body)
                                <p class="mt-1 text-xs text-zinc-500 whitespace-pre-wrap">{{ Str::limit($r->body, 200) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-400">{{ $r->user->name }}</td>
                        <td class="px-4 py-3 text-xs text-zinc-400">{{ \App\Models\ProjectRequest::statusLabels()[$r->status] ?? $r->status }}</td>
                        <td class="px-4 py-3 text-xs">
                            @if ($r->supportTicket)
                                <span class="text-cyan-400">#{{ $r->support_ticket_id }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $project)
                                <details class="inline text-left">
                                    <summary class="cursor-pointer text-xs text-cyan-400">Gérer</summary>
                                    <form method="POST" action="{{ route('portals.project.requests.update', [$project, $r]) }}" class="mt-2 space-y-2 rounded border border-zinc-700 bg-zinc-950 p-3">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                            @foreach (\App\Models\ProjectRequest::statusLabels() as $val => $lab)
                                                <option value="{{ $val }}" @selected($r->status === $val)>{{ $lab }}</option>
                                            @endforeach
                                        </select>
                                        <select name="support_ticket_id" class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-white">
                                            <option value="">— Pas de ticket</option>
                                            @foreach ($supportTickets as $t)
                                                <option value="{{ $t->id }}" @selected($r->support_ticket_id === $t->id)>#{{ $t->id }} — {{ Str::limit($t->subject, 40) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="text-xs font-semibold text-cyan-400">Enregistrer</button>
                                    </form>
                                    <form method="POST" action="{{ route('portals.project.requests.destroy', [$project, $r]) }}" class="mt-2" onsubmit="return confirm('Supprimer cette demande ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400">Supprimer</button>
                                    </form>
                                </details>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-zinc-500">Aucune demande.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($requests->hasPages())
            <div class="border-t border-zinc-800 px-4 py-3">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection
