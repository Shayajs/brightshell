@use('App\Support\BrightshellDomain')

@extends('layouts.agenda')

@section('title', 'Prendre rendez-vous')

@section('content')
<div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col px-4 py-6 sm:px-8 sm:py-10">
    <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ BrightshellDomain::publicSiteUrl() }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 transition hover:text-indigo-300">← BrightShell</a>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">Prendre rendez-vous</h1>
            <p class="mt-1 text-sm text-zinc-400">Sélectionnez une date puis un créneau. Je vous confirme rapidement.</p>
        </div>
        @if ($previewMode)
            <a href="{{ route('agenda.index') }}"
               class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-2.5 text-sm font-semibold text-amber-200 transition hover:bg-amber-500/20">
                ← Retour à la gestion
            </a>
        @endif
    </header>

    @if ($previewMode)
        <div class="mb-5 flex items-center gap-2 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Mode aperçu visiteur — la réservation est désactivée.
        </div>
    @endif

    @include('layouts.partials.flash')

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid flex-1 gap-6 lg:grid-cols-[1.45fr_1fr]">
        {{-- Calendrier --}}
        <section class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-4 ring-1 ring-white/5 sm:p-6">
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

            <div class="mt-4 flex items-center gap-4 text-xs text-zinc-500">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Disponible</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-zinc-700"></span> Aucun créneau</span>
            </div>
        </section>

        {{-- Panneau créneaux + formulaire --}}
        <section class="flex flex-col gap-4">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 ring-1 ring-white/5">
                <h2 id="slots-title" class="font-display text-sm font-bold text-white">Sélectionnez une date</h2>
                <p id="slots-empty" class="mt-2 text-sm text-zinc-500">Les dates avec un point sont réservables.</p>
                <div id="slots-list" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3"></div>
            </div>

            <form id="booking-form" method="post" action="{{ route('agenda.book') }}" class="hidden rounded-2xl border border-indigo-500/25 bg-zinc-900/60 p-5 ring-1 ring-indigo-500/10">
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                <input type="hidden" name="appointment_slot_id" id="field-slot-id" value="{{ old('appointment_slot_id') }}">

                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="font-display text-sm font-bold text-white">Vos coordonnées</h2>
                    <span id="chosen-slot" class="rounded-lg border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-1 text-xs font-semibold text-indigo-200"></span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                            class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                </div>
                <div class="mt-3">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Téléphone <span class="normal-case font-normal text-zinc-600">(optionnel)</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                </div>
                <div class="mt-3">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Message <span class="normal-case font-normal text-zinc-600">(optionnel)</span></label>
                    <textarea name="message" rows="3" maxlength="2000"
                        class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">{{ old('message') }}</textarea>
                </div>

                <button type="submit" id="booking-submit" @if($previewMode) disabled @endif
                    class="mt-4 w-full rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                    @if ($previewMode) Réservation désactivée (aperçu) @else Confirmer le rendez-vous @endif
                </button>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const SLOTS = @json($slots);
    const OLD_SLOT_ID = @json((string) old('appointment_slot_id'));
    const MONTHS = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    const byDay = {};
    SLOTS.forEach(s => { (byDay[s.day] = byDay[s.day] || []).push(s); });
    Object.values(byDay).forEach(list => list.sort((a, b) => a.start.localeCompare(b.start)));

    const grid = document.getElementById('cal-grid');
    const monthLabel = document.getElementById('cal-month');
    const slotsTitle = document.getElementById('slots-title');
    const slotsEmpty = document.getElementById('slots-empty');
    const slotsList = document.getElementById('slots-list');
    const bookingForm = document.getElementById('booking-form');
    const fieldSlotId = document.getElementById('field-slot-id');
    const chosenSlot = document.getElementById('chosen-slot');

    const today = new Date(); today.setHours(0, 0, 0, 0);
    let view = new Date(today.getFullYear(), today.getMonth(), 1);
    let selectedDay = null;

    const pad = n => String(n).padStart(2, '0');
    const ymd = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const prettyDay = str => {
        const [y, m, d] = str.split('-').map(Number);
        const dt = new Date(y, m - 1, d);
        return dt.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
    };

    function renderMonth() {
        monthLabel.textContent = `${MONTHS[view.getMonth()]} ${view.getFullYear()}`;
        grid.innerHTML = '';

        const first = new Date(view.getFullYear(), view.getMonth(), 1);
        let offset = (first.getDay() + 6) % 7; // lundi = 0
        const daysInMonth = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();

        for (let i = 0; i < offset; i++) {
            const filler = document.createElement('div');
            grid.appendChild(filler);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(view.getFullYear(), view.getMonth(), day);
            const key = ymd(date);
            const isPast = date < today;
            const list = byDay[key] || [];
            const available = list.length > 0 && !isPast;

            const cell = document.createElement('button');
            cell.type = 'button';
            cell.textContent = day;
            cell.dataset.day = key;

            if (available) {
                cell.className = 'relative aspect-square rounded-xl border border-zinc-700 bg-zinc-950/60 text-sm font-semibold text-zinc-100 transition hover:border-indigo-500/60 hover:bg-indigo-500/10';
                const dot = document.createElement('span');
                dot.className = 'absolute bottom-1.5 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-indigo-500';
                cell.appendChild(dot);
                cell.addEventListener('click', () => selectDay(key));
            } else {
                cell.disabled = true;
                cell.className = 'aspect-square rounded-xl border border-transparent text-sm text-zinc-600 ' + (isPast ? 'line-through opacity-40' : '');
            }

            if (key === ymd(today)) {
                cell.classList.add('ring-1', 'ring-inset', 'ring-indigo-400/40');
            }
            if (key === selectedDay) {
                cell.classList.add('!border-indigo-500', '!bg-indigo-500/20');
            }

            grid.appendChild(cell);
        }
    }

    function selectDay(key) {
        selectedDay = key;
        renderMonth();

        const list = byDay[key] || [];
        slotsTitle.textContent = prettyDay(key);
        slotsList.innerHTML = '';

        if (list.length === 0) {
            slotsEmpty.classList.remove('hidden');
            slotsEmpty.textContent = 'Aucun créneau ce jour.';
            bookingForm.classList.add('hidden');
            return;
        }

        slotsEmpty.classList.add('hidden');
        list.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = `${slot.start} – ${slot.end}`;
            btn.className = 'rounded-lg border border-zinc-700 bg-zinc-950/60 px-2 py-2 text-sm font-semibold text-zinc-200 transition hover:border-indigo-500/60 hover:bg-indigo-500/10';
            btn.addEventListener('click', () => chooseSlot(slot, btn));
            slotsList.appendChild(btn);
        });
    }

    function chooseSlot(slot, btn) {
        slotsList.querySelectorAll('button').forEach(b => b.classList.remove('!border-indigo-500', '!bg-indigo-500/20', '!text-white'));
        btn.classList.add('!border-indigo-500', '!bg-indigo-500/20', '!text-white');
        fieldSlotId.value = slot.id;
        chosenSlot.textContent = `${prettyDay(slot.day)} · ${slot.start}`;
        bookingForm.classList.remove('hidden');
        bookingForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    document.getElementById('cal-prev').addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() - 1, 1);
        renderMonth();
    });
    document.getElementById('cal-next').addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() + 1, 1);
        renderMonth();
    });
    document.getElementById('cal-today').addEventListener('click', () => {
        view = new Date(today.getFullYear(), today.getMonth(), 1);
        renderMonth();
    });

    renderMonth();

    // Ré-ouverture après erreur de validation
    if (OLD_SLOT_ID) {
        const slot = SLOTS.find(s => String(s.id) === OLD_SLOT_ID);
        if (slot) {
            view = new Date(...slot.day.split('-').map((v, i) => i === 1 ? Number(v) - 1 : Number(v)));
            view.setDate(1);
            selectDay(slot.day);
            const target = Array.from(slotsList.querySelectorAll('button')).find(b => b.textContent === `${slot.start} – ${slot.end}`);
            if (target) chooseSlot(slot, target);
        }
    }
})();
</script>
@endpush
