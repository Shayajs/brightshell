@extends('layouts.admin')
@section('title', 'Tickets & demandes')
@section('topbar_label', 'Support')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">Tickets & demandes</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $tickets->total() }} ticket(s)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach (['open' => 'Ouverts', 'in_progress' => 'En cours', 'closed' => 'Clos'] as $st => $label)
                <a href="{{ route('admin.support-tickets.index', ['status' => $st]) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ ($status ?? 'open') === $st ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Sujet</th>
                        <th class="px-5 py-3">Catégorie</th>
                        <th class="px-5 py-3">Portée</th>
                        <th class="px-5 py-3">E-mail</th>
                        <th class="px-5 py-3">Statut</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($tickets as $ticket)
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5 font-mono text-xs text-zinc-500">{{ $ticket->id }}</td>
                            <td class="px-5 py-3.5 text-zinc-200">{{ \Illuminate\Support\Str::limit($ticket->subject, 48) }}</td>
                            <td class="px-5 py-3.5 text-zinc-400">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}</td>
                            <td class="px-5 py-3.5 text-zinc-400">
                                @if ($ticket->company)
                                    <span class="text-zinc-300">{{ $ticket->company->name }}</span>
                                @else
                                    <span class="text-zinc-500">Personnel</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">{{ $ticket->email }}</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-md border border-zinc-700 bg-zinc-950 px-2 py-0.5 text-xs text-zinc-300">{{ $ticket->status }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-500">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-zinc-500">Aucun ticket pour ce filtre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
