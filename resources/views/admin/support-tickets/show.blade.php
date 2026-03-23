@extends('layouts.admin')
@section('title', 'Ticket #'.$ticket->id)
@section('topbar_label', 'Support')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.support-tickets.index', ['status' => $ticket->status]) }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Tickets</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">#{{ $ticket->id }}</span>
    </div>

    @include('layouts.partials.flash')

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h1 class="font-display text-lg font-bold text-white">{{ $ticket->subject }}</h1>
                <p class="mt-2 text-sm text-zinc-500">Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}</p>
                <div class="mt-6 border-t border-zinc-800 pt-6 text-sm leading-relaxed text-zinc-300">
                    @if ($ticket->body)
                        <p class="whitespace-pre-wrap">{{ $ticket->body }}</p>
                    @else
                        <p class="text-zinc-500 italic">(Pas de message complémentaire.)</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Détails</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">E-mail</dt>
                        <dd class="text-right font-mono text-xs text-zinc-300">{{ $ticket->email }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">Catégorie</dt>
                        <dd class="text-right text-zinc-300">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">Portée</dt>
                        <dd class="text-right text-zinc-300">
                            @if ($ticket->company)
                                {{ $ticket->company->name }}
                            @else
                                Compte personnel
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">Statut</dt>
                        <dd>
                            <form method="post" action="{{ route('admin.support-tickets.update', $ticket) }}" class="flex flex-col items-end gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-lg border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-xs text-zinc-200" onchange="this.form.submit()">
                                    @foreach (['open' => 'Ouvert', 'in_progress' => 'En cours', 'closed' => 'Clos'] as $val => $label)
                                        <option value="{{ $val }}" @selected($ticket->status === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </dd>
                    </div>
                </dl>

                @if ($ticket->user)
                    <div class="mt-6 border-t border-zinc-800 pt-6">
                        <a href="{{ route('admin.members.show', $ticket->user) }}" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300">Voir le membre →</a>
                    </div>
                @endif

                @can('verifyMemberEmail', $ticket)
                    @if ($ticket->user && ! $ticket->user->hasVerifiedEmail())
                        <form method="post" action="{{ route('admin.support-tickets.verify-email', $ticket) }}" class="mt-6" onsubmit="return confirm('Confirmer manuellement l’adresse e-mail de ce compte ?');">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300 transition hover:bg-emerald-500/20">
                                Confirmer l’e-mail manuellement
                            </button>
                        </form>
                    @elseif ($ticket->user && $ticket->user->hasVerifiedEmail())
                        <p class="mt-6 text-xs text-zinc-500">Compte déjà confirmé.</p>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
