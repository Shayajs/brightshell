@use('App\Support\BrightshellDomain')

@extends('layouts.agenda')

@section('title', 'Agenda — Gestion')

@section('content')
<div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 sm:px-8 sm:py-8">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ BrightshellDomain::publicSiteUrl() }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 transition hover:text-indigo-300">← BrightShell</a>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">Agenda — Gestion</h1>
            <p class="mt-1 text-sm text-zinc-400">Ajoutez des créneaux réservables et bloquez vos indisponibilités (RDV, boulot).</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('agenda.preview') }}" target="_blank" class="flex items-center gap-2 rounded-xl border border-amber-500/40 bg-amber-500/10 px-3 py-2.5 text-sm font-semibold text-amber-200 transition hover:bg-amber-500/20">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Aperçu visiteur
            </a>
            <button type="button" id="btn-busy" class="rounded-xl border border-zinc-700 px-3 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-zinc-500 hover:text-white">Occupé / indispo</button>
            <button type="button" id="btn-bulk" class="rounded-xl border border-zinc-700 px-3 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-indigo-500/50 hover:text-indigo-300">Générer une plage</button>
            <button type="button" id="btn-new" class="rounded-xl border border-indigo-500/40 bg-indigo-600/90 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">+ Créneau</button>
        </div>
    </header>

    @include('layouts.partials.flash')

    @include('agenda.partials.toolbar')

    <section class="flex-1 rounded-2xl border border-zinc-800 bg-zinc-900/40 p-3 ring-1 ring-white/5 sm:p-5">
        <div id="cal-root"></div>
        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-zinc-500">
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Disponible</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Réservé</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Chevauché par une indispo</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-zinc-600"></span> Bloqué / occupé</span>
        </div>
    </section>
</div>

{{-- Modale : nouveau créneau --}}
<div id="modal-new" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between"><h2 class="font-display text-lg font-bold text-white">Nouveau créneau</h2><button type="button" class="modal-close text-zinc-500 hover:text-white">✕</button></div>
        <form method="POST" action="{{ route('agenda.slots.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="mode" value="single">
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Début</label><input type="datetime-local" name="starts_at" id="new-start" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Fin</label><input type="datetime-local" name="ends_at" id="new-end" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes (interne)</label><input type="text" name="notes" placeholder="Visio, téléphone…" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <button type="submit" class="w-full rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">Ajouter</button>
        </form>
    </div>
</div>

{{-- Modale : générer une plage --}}
<div id="modal-bulk" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between"><h2 class="font-display text-lg font-bold text-white">Générer une plage</h2><button type="button" class="modal-close text-zinc-500 hover:text-white">✕</button></div>
        <form method="POST" action="{{ route('agenda.slots.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="mode" value="bulk">
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Date</label><input type="date" name="bulk_date" id="bulk-date" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <div class="grid grid-cols-2 gap-2">
                <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">De</label><input type="time" name="bulk_from" value="09:00" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">À</label><input type="time" name="bulk_to" value="17:00" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            </div>
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Durée par créneau</label>
                <select name="bulk_duration" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    <option value="15">15 min</option><option value="30" selected>30 min</option><option value="45">45 min</option><option value="60">60 min</option>
                </select>
            </div>
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes (interne)</label><input type="text" name="notes" placeholder="Optionnel" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <button type="submit" class="w-full rounded-lg border border-dashed border-zinc-600 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-indigo-500/60 hover:text-indigo-300">Générer</button>
        </form>
    </div>
</div>

{{-- Modale : indisponibilité --}}
<div id="modal-busy" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between"><h2 class="font-display text-lg font-bold text-white">Indisponibilité (occupé)</h2><button type="button" class="modal-close text-zinc-500 hover:text-white">✕</button></div>
        <p class="mb-4 text-xs text-zinc-500">Grise automatiquement les créneaux réservables qui chevauchent cette période.</p>
        <form method="POST" action="{{ route('agenda.busy.store') }}" class="space-y-3">
            @csrf
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Début</label><input type="datetime-local" name="starts_at" id="busy-start" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Fin</label><input type="datetime-local" name="ends_at" id="busy-end" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <div><label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Intitulé (optionnel)</label><input type="text" name="title" placeholder="RDV client, boulot…" maxlength="150" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"></div>
            <button type="submit" class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm font-semibold text-zinc-200 transition hover:bg-zinc-700">Marquer occupé</button>
        </form>
    </div>
</div>

{{-- Modale : détail créneau --}}
<div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between"><h2 id="detail-title" class="font-display text-lg font-bold text-white">Créneau</h2><button type="button" class="modal-close text-zinc-500 hover:text-white">✕</button></div>
        <div id="detail-status" class="mb-3"></div>
        <div id="detail-booking" class="mb-4 hidden rounded-xl border border-zinc-800 bg-zinc-950/60 p-4 text-sm"></div>
        <div class="flex flex-wrap gap-2">
            <form id="detail-toggle-form" method="POST" class="flex-1">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="detail-toggle-value">
                <button type="submit" id="detail-toggle-btn" class="w-full rounded-lg border border-zinc-700 px-3 py-2 text-sm font-semibold text-zinc-300 transition hover:border-zinc-600 hover:text-white"></button>
            </form>
            <form id="detail-delete-form" method="POST" class="flex-1" onsubmit="return confirm('Supprimer ce créneau ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full rounded-lg border border-red-500/30 px-3 py-2 text-sm font-semibold text-red-400 transition hover:bg-red-500/10">Supprimer</button>
            </form>
        </div>
        <p id="detail-locked" class="mt-3 hidden text-xs text-zinc-500">Un créneau réservé ne peut être ni bloqué ni supprimé.</p>
    </div>
</div>

{{-- Modale : détail indisponibilité --}}
<div id="modal-busy-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between"><h2 id="busy-detail-title" class="font-display text-lg font-bold text-white">Indisponibilité</h2><button type="button" class="modal-close text-zinc-500 hover:text-white">✕</button></div>
        <div id="busy-detail-body" class="mb-4 text-sm text-zinc-300"></div>
        <form id="busy-delete-form" method="POST" onsubmit="return confirm('Supprimer cette indisponibilité ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full rounded-lg border border-red-500/30 px-3 py-2 text-sm font-semibold text-red-400 transition hover:bg-red-500/10">Supprimer l'indisponibilité</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.__AGENDA__ = {
    slots: @json($slots),
    busy: @json($busy),
    mode: 'admin',
};
</script>
@include('agenda.partials.calendar-js')
<script>
(function () {
    const SLOT_UPDATE_URL = @json(route('agenda.slots.update', ['slot' => '__ID__']));
    const SLOT_DELETE_URL = @json(route('agenda.slots.destroy', ['slot' => '__ID__']));
    const BUSY_DELETE_URL = @json(route('agenda.busy.destroy', ['block' => '__ID__']));
    const STATUS = {
        open:    { label: 'Disponible', dot: 'bg-emerald-500', chip: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' },
        booked:  { label: 'Réservé',    dot: 'bg-indigo-500',  chip: 'border-indigo-500/30 bg-indigo-500/10 text-indigo-200' },
        blocked: { label: 'Bloqué',     dot: 'bg-zinc-600',    chip: 'border-zinc-600 bg-zinc-800 text-zinc-400' },
    };

    document.getElementById('btn-new').addEventListener('click', () => AgendaCal.showModal('modal-new'));
    document.getElementById('btn-bulk').addEventListener('click', () => AgendaCal.showModal('modal-bulk'));
    document.getElementById('btn-busy').addEventListener('click', () => AgendaCal.showModal('modal-busy'));

    window.agendaOnSlotClick = function (slot) {
        const meta = STATUS[slot.status] || STATUS.open;
        document.getElementById('detail-title').textContent = `${AgendaCal.prettyDay(slot.day)} · ${slot.start} – ${slot.end}`;
        document.getElementById('detail-status').innerHTML =
            `<span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-semibold ${meta.chip}"><span class="h-2 w-2 rounded-full ${meta.dot}"></span>${meta.label}</span>`
            + (slot.busy && slot.status === 'open' ? `<span class="ml-2 rounded-lg border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-200">Chevauché par une indispo</span>` : '')
            + (slot.notes ? `<p class="mt-2 text-xs text-zinc-500">${slot.notes}</p>` : '');

        const box = document.getElementById('detail-booking');
        if (slot.booking) {
            box.classList.remove('hidden');
            box.innerHTML = `<p class="font-semibold text-zinc-200">${slot.booking.name}</p>`
                + `<p class="mt-0.5 font-mono text-xs text-indigo-300">${slot.booking.email}</p>`
                + (slot.booking.phone ? `<p class="text-xs text-zinc-400">${slot.booking.phone}</p>` : '')
                + (slot.booking.message ? `<p class="mt-2 whitespace-pre-wrap text-xs text-zinc-400">${slot.booking.message}</p>` : '');
        } else { box.classList.add('hidden'); box.innerHTML = ''; }

        const toggleForm = document.getElementById('detail-toggle-form');
        const deleteForm = document.getElementById('detail-delete-form');
        const locked = document.getElementById('detail-locked');
        if (slot.status === 'booked') {
            toggleForm.classList.add('hidden'); deleteForm.classList.add('hidden'); locked.classList.remove('hidden');
        } else {
            toggleForm.classList.remove('hidden'); deleteForm.classList.remove('hidden'); locked.classList.add('hidden');
            toggleForm.action = SLOT_UPDATE_URL.replace('__ID__', slot.id);
            deleteForm.action = SLOT_DELETE_URL.replace('__ID__', slot.id);
            document.getElementById('detail-toggle-value').value = slot.status === 'blocked' ? 'open' : 'blocked';
            document.getElementById('detail-toggle-btn').textContent = slot.status === 'blocked' ? 'Rouvrir' : 'Bloquer';
        }
        AgendaCal.showModal('modal-detail');
    };

    window.agendaOnBusyClick = function (block) {
        document.getElementById('busy-detail-title').textContent = block.title ? ('Occupé · ' + block.title) : 'Indisponibilité';
        document.getElementById('busy-detail-body').textContent = `${AgendaCal.prettyDay(block.day)} · ${block.start} – ${block.end}`;
        document.getElementById('busy-delete-form').action = BUSY_DELETE_URL.replace('__ID__', block.id);
        AgendaCal.showModal('modal-busy-detail');
    };

    AgendaCal.init();
})();
</script>
@endpush
