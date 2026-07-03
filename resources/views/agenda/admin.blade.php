@use('App\Support\BrightshellDomain')

@extends('layouts.agenda')

@section('title', 'Agenda — Gestion')

@section('content')
<div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 sm:px-8 sm:py-8">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ BrightshellDomain::publicSiteUrl() }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 transition hover:text-indigo-300">← BrightShell</a>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">Agenda — Gestion</h1>
            <p class="mt-1 text-sm text-zinc-400">Cliquez un jour pour ajouter un créneau, ou un créneau pour le gérer.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('agenda.preview') }}" target="_blank"
               class="flex items-center gap-2 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-2.5 text-sm font-semibold text-amber-200 transition hover:bg-amber-500/20">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Aperçu visiteur
            </a>
            <button type="button" id="btn-bulk" class="rounded-xl border border-zinc-700 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-indigo-500/50 hover:text-indigo-300">Générer une plage</button>
            <button type="button" id="btn-new" class="rounded-xl border border-indigo-500/40 bg-indigo-600/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">+ Nouveau créneau</button>
        </div>
    </header>

    @include('layouts.partials.flash')

    <section class="flex-1 rounded-2xl border border-zinc-800 bg-zinc-900/40 p-4 ring-1 ring-white/5 sm:p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <button type="button" id="cal-prev" class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-700 text-zinc-400 transition hover:border-indigo-500/50 hover:text-indigo-300" aria-label="Mois précédent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="text-center">
                <p id="cal-month" class="font-display text-lg font-bold capitalize text-white"></p>
                <button type="button" id="cal-today" class="text-[11px] font-semibold uppercase tracking-wider text-indigo-400 hover:text-indigo-300">Aujourd'hui</button>
            </div>
            <button type="button" id="cal-next" class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-700 text-zinc-400 transition hover:border-indigo-500/50 hover:text-indigo-300" aria-label="Mois suivant">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <div class="mb-2 grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wide text-zinc-500">
            <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
        </div>
        <div id="cal-grid" class="grid grid-cols-7 gap-1"></div>

        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-zinc-500">
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Disponible</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Réservé</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-zinc-600"></span> Bloqué</span>
        </div>
    </section>
</div>

{{-- Modale : nouveau créneau --}}
<div id="modal-new" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-bold text-white">Nouveau créneau</h2>
            <button type="button" class="modal-close text-zinc-500 hover:text-white" aria-label="Fermer">✕</button>
        </div>
        <form method="POST" action="{{ route('agenda.slots.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="mode" value="single">
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Début</label>
                <input type="datetime-local" name="starts_at" id="new-start" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Fin</label>
                <input type="datetime-local" name="ends_at" id="new-end" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes (interne)</label>
                <input type="text" name="notes" placeholder="Visio, téléphone…" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            </div>
            <button type="submit" class="w-full rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">Ajouter</button>
        </form>
    </div>
</div>

{{-- Modale : générer une plage --}}
<div id="modal-bulk" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-bold text-white">Générer une plage</h2>
            <button type="button" class="modal-close text-zinc-500 hover:text-white" aria-label="Fermer">✕</button>
        </div>
        <form method="POST" action="{{ route('agenda.slots.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="mode" value="bulk">
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Date</label>
                <input type="date" name="bulk_date" id="bulk-date" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">De</label>
                    <input type="time" name="bulk_from" value="09:00" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">À</label>
                    <input type="time" name="bulk_to" value="17:00" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Durée par créneau</label>
                <select name="bulk_duration" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    <option value="15">15 min</option>
                    <option value="30" selected>30 min</option>
                    <option value="45">45 min</option>
                    <option value="60">60 min</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Notes (interne)</label>
                <input type="text" name="notes" placeholder="Optionnel" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            </div>
            <button type="submit" class="w-full rounded-lg border border-dashed border-zinc-600 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-indigo-500/60 hover:text-indigo-300">Générer</button>
        </form>
    </div>
</div>

{{-- Modale : détail d'un créneau --}}
<div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between">
            <h2 id="detail-title" class="font-display text-lg font-bold text-white">Créneau</h2>
            <button type="button" class="modal-close text-zinc-500 hover:text-white" aria-label="Fermer">✕</button>
        </div>
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
@endsection

@push('scripts')
<script>
(function () {
    const SLOTS = @json($slots);
    const SLOT_UPDATE_URL = @json(route('agenda.slots.update', ['slot' => '__ID__']));
    const SLOT_DELETE_URL = @json(route('agenda.slots.destroy', ['slot' => '__ID__']));
    const MONTHS = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const STATUS = {
        open:    { label: 'Disponible', dot: 'bg-emerald-500', chip: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' },
        booked:  { label: 'Réservé',    dot: 'bg-indigo-500',  chip: 'border-indigo-500/30 bg-indigo-500/10 text-indigo-200' },
        blocked: { label: 'Bloqué',     dot: 'bg-zinc-600',    chip: 'border-zinc-600 bg-zinc-800 text-zinc-400' },
    };

    const byDay = {};
    SLOTS.forEach(s => { (byDay[s.day] = byDay[s.day] || []).push(s); });
    Object.values(byDay).forEach(list => list.sort((a, b) => a.start.localeCompare(b.start)));

    const grid = document.getElementById('cal-grid');
    const monthLabel = document.getElementById('cal-month');
    const today = new Date(); today.setHours(0, 0, 0, 0);
    let view = new Date(today.getFullYear(), today.getMonth(), 1);

    const pad = n => String(n).padStart(2, '0');
    const ymd = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const prettyDay = str => {
        const [y, m, d] = str.split('-').map(Number);
        return new Date(y, m - 1, d).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
    };

    function renderMonth() {
        monthLabel.textContent = `${MONTHS[view.getMonth()]} ${view.getFullYear()}`;
        grid.innerHTML = '';

        const first = new Date(view.getFullYear(), view.getMonth(), 1);
        const offset = (first.getDay() + 6) % 7;
        const daysInMonth = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();

        for (let i = 0; i < offset; i++) grid.appendChild(document.createElement('div'));

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(view.getFullYear(), view.getMonth(), day);
            const key = ymd(date);
            const list = byDay[key] || [];

            const cell = document.createElement('div');
            cell.className = 'group flex min-h-24 flex-col rounded-xl border border-zinc-800 bg-zinc-950/40 p-1.5 transition hover:border-zinc-700';
            if (key === ymd(today)) cell.classList.add('ring-1', 'ring-inset', 'ring-indigo-400/40');

            const head = document.createElement('div');
            head.className = 'mb-1 flex items-center justify-between px-1';
            const num = document.createElement('span');
            num.className = 'text-xs font-semibold text-zinc-400';
            num.textContent = day;
            const add = document.createElement('button');
            add.type = 'button';
            add.textContent = '+';
            add.className = 'text-sm leading-none text-zinc-600 opacity-0 transition hover:text-indigo-300 group-hover:opacity-100';
            add.addEventListener('click', () => openNew(key));
            head.appendChild(num);
            head.appendChild(add);
            cell.appendChild(head);

            list.forEach(slot => {
                const meta = STATUS[slot.status] || STATUS.open;
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = `mb-1 w-full truncate rounded-md border px-1.5 py-0.5 text-left text-[11px] font-semibold ${meta.chip}`;
                chip.textContent = `${slot.start} ${slot.booking ? '· ' + slot.booking.name : ''}`.trim();
                chip.addEventListener('click', () => openDetail(slot));
                cell.appendChild(chip);
            });

            grid.appendChild(cell);
        }
    }

    // Modales
    function show(id) { const m = document.getElementById(id); m.classList.remove('hidden'); m.classList.add('flex'); }
    function hide(el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    document.querySelectorAll('.modal-close').forEach(b => b.addEventListener('click', () => hide(b.closest('.fixed'))));
    document.querySelectorAll('.fixed.inset-0').forEach(m => m.addEventListener('click', e => { if (e.target === m) hide(m); }));

    function openNew(key) {
        const start = document.getElementById('new-start');
        const end = document.getElementById('new-end');
        if (key) {
            start.value = `${key}T09:00`;
            end.value = `${key}T09:30`;
        }
        show('modal-new');
    }
    document.getElementById('btn-new').addEventListener('click', () => openNew(null));
    document.getElementById('btn-bulk').addEventListener('click', () => show('modal-bulk'));

    function openDetail(slot) {
        const meta = STATUS[slot.status] || STATUS.open;
        document.getElementById('detail-title').textContent = `${prettyDay(slot.day)} · ${slot.start} – ${slot.end}`;
        document.getElementById('detail-status').innerHTML =
            `<span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-semibold ${meta.chip}"><span class="h-2 w-2 rounded-full ${meta.dot}"></span>${meta.label}</span>`
            + (slot.notes ? `<p class="mt-2 text-xs text-zinc-500">${slot.notes}</p>` : '');

        const bookingBox = document.getElementById('detail-booking');
        if (slot.booking) {
            bookingBox.classList.remove('hidden');
            bookingBox.innerHTML =
                `<p class="font-semibold text-zinc-200">${slot.booking.name}</p>`
                + `<p class="mt-0.5 font-mono text-xs text-indigo-300">${slot.booking.email}</p>`
                + (slot.booking.phone ? `<p class="text-xs text-zinc-400">${slot.booking.phone}</p>` : '')
                + (slot.booking.message ? `<p class="mt-2 whitespace-pre-wrap text-xs text-zinc-400">${slot.booking.message}</p>` : '');
        } else {
            bookingBox.classList.add('hidden');
            bookingBox.innerHTML = '';
        }

        const toggleForm = document.getElementById('detail-toggle-form');
        const deleteForm = document.getElementById('detail-delete-form');
        const toggleBtn = document.getElementById('detail-toggle-btn');
        const toggleValue = document.getElementById('detail-toggle-value');
        const locked = document.getElementById('detail-locked');

        if (slot.status === 'booked') {
            toggleForm.classList.add('hidden');
            deleteForm.classList.add('hidden');
            locked.classList.remove('hidden');
        } else {
            toggleForm.classList.remove('hidden');
            deleteForm.classList.remove('hidden');
            locked.classList.add('hidden');
            toggleForm.action = SLOT_UPDATE_URL.replace('__ID__', slot.id);
            deleteForm.action = SLOT_DELETE_URL.replace('__ID__', slot.id);
            toggleValue.value = slot.status === 'blocked' ? 'open' : 'blocked';
            toggleBtn.textContent = slot.status === 'blocked' ? 'Rouvrir' : 'Bloquer';
        }

        show('modal-detail');
    }

    document.getElementById('cal-prev').addEventListener('click', () => { view = new Date(view.getFullYear(), view.getMonth() - 1, 1); renderMonth(); });
    document.getElementById('cal-next').addEventListener('click', () => { view = new Date(view.getFullYear(), view.getMonth() + 1, 1); renderMonth(); });
    document.getElementById('cal-today').addEventListener('click', () => { view = new Date(today.getFullYear(), today.getMonth(), 1); renderMonth(); });

    renderMonth();
})();
</script>
@endpush
