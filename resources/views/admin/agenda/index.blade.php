@use('App\Models\AppointmentSlot')

@extends('layouts.admin')
@section('title', 'Agenda — Créneaux')
@section('topbar_label', 'Agenda')

@section('content')
<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-white">Créneaux de rendez-vous</h1>
            <p class="mt-1 text-sm text-zinc-500">Créez des créneaux disponibles sur la page publique <a href="{{ route('appointments') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300">/rendez-vous</a>.</p>
        </div>
    </div>

    @include('layouts.partials.flash')

    <div class="grid gap-6 xl:grid-cols-[22rem_1fr]">
        {{-- Formulaire --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold text-white">Créneau unique</h2>
                <form method="POST" action="{{ route('admin.agenda.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="mode" value="single">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Début</label>
                        <input type="datetime-local" name="starts_at" required
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Fin</label>
                        <input type="datetime-local" name="ends_at" required
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes (interne)</label>
                        <input type="text" name="notes" placeholder="Visio, téléphone…"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                    <button type="submit" class="w-full rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        Ajouter le créneau
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-sm font-bold text-white">Générer une plage</h2>
                <p class="mt-1 text-xs text-zinc-500">Découpe automatiquement une journée en créneaux.</p>
                <form method="POST" action="{{ route('admin.agenda.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="mode" value="bulk">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Date</label>
                        <input type="date" name="bulk_date" required
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">De</label>
                            <input type="time" name="bulk_from" value="09:00" required
                                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">À</label>
                            <input type="time" name="bulk_to" value="17:00" required
                                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Durée par créneau</label>
                        <select name="bulk_duration" required
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                            <option value="15">15 min</option>
                            <option value="30" selected>30 min</option>
                            <option value="45">45 min</option>
                            <option value="60">60 min</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes (interne)</label>
                        <input type="text" name="notes" placeholder="Optionnel"
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                    <button type="submit" class="w-full rounded-lg border border-dashed border-zinc-600 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-indigo-500/60 hover:text-indigo-300">
                        Générer les créneaux
                    </button>
                </form>
            </div>
        </div>

        {{-- Liste --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                            <th class="px-5 py-3">Créneau</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3">Réservation</th>
                            <th class="px-5 py-3">Notes</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @forelse ($slots as $slot)
                            <tr class="transition hover:bg-zinc-800/30">
                                <td class="px-5 py-3.5 text-zinc-200">{{ $slot->formattedRange() }}</td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $badge = match ($slot->status) {
                                            AppointmentSlot::STATUS_OPEN => 'text-emerald-400 border-emerald-500/30 bg-emerald-500/10',
                                            AppointmentSlot::STATUS_BOOKED => 'text-indigo-300 border-indigo-500/30 bg-indigo-500/10',
                                            default => 'text-zinc-400 border-zinc-600 bg-zinc-800',
                                        };
                                    @endphp
                                    <span class="rounded-md border px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $slot->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($slot->booking)
                                        <div class="text-zinc-300">{{ $slot->booking->fullName() }}</div>
                                        <div class="font-mono text-xs text-zinc-500">{{ $slot->booking->email }}</div>
                                    @else
                                        <span class="text-zinc-600">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-500">{{ $slot->notes ?: '—' }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($slot->status !== AppointmentSlot::STATUS_BOOKED)
                                            <form method="POST" action="{{ route('admin.agenda.update', $slot) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $slot->status === AppointmentSlot::STATUS_BLOCKED ? AppointmentSlot::STATUS_OPEN : AppointmentSlot::STATUS_BLOCKED }}">
                                                <button type="submit" class="rounded-lg border border-zinc-700 px-2.5 py-1 text-xs text-zinc-400 transition hover:border-zinc-600 hover:text-zinc-200">
                                                    {{ $slot->status === AppointmentSlot::STATUS_BLOCKED ? 'Rouvrir' : 'Bloquer' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.agenda.destroy', $slot) }}" onsubmit="return confirm('Supprimer ce créneau ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-500/30 px-2.5 py-1 text-xs text-red-400 transition hover:bg-red-500/10">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-zinc-500">Aucun créneau à venir. Ajoutez-en via le formulaire.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($slots->hasPages())
                <div class="border-t border-zinc-800 px-5 py-3">{{ $slots->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
