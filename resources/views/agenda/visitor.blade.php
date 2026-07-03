@use('App\Support\BrightshellDomain')

@extends('layouts.agenda')

@section('title', 'Prendre rendez-vous')

@section('content')
<div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 sm:px-8 sm:py-10">
    <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ BrightshellDomain::publicSiteUrl() }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 transition hover:text-indigo-300">← BrightShell</a>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">Prendre rendez-vous</h1>
            <p class="mt-1 text-sm text-zinc-400">Cliquez un créneau disponible pour réserver.</p>
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
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @include('agenda.partials.toolbar')

    <section class="flex-1 rounded-2xl border border-zinc-800 bg-zinc-900/40 p-3 ring-1 ring-white/5 sm:p-5">
        <div id="cal-root"></div>
        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-zinc-500">
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Disponible</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-zinc-700"></span> Occupé / indisponible</span>
        </div>
    </section>
</div>

{{-- Modale de réservation --}}
<div id="modal-book" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-indigo-500/25 bg-zinc-900 p-6 ring-1 ring-white/10">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-display text-lg font-bold text-white">Vos coordonnées</h2>
            <button type="button" class="modal-close text-zinc-500 hover:text-white" aria-label="Fermer">✕</button>
        </div>
        <p id="chosen-slot" class="mb-4 rounded-lg border border-indigo-500/30 bg-indigo-500/10 px-3 py-2 text-sm font-semibold text-indigo-200"></p>
        <form method="post" action="{{ route('agenda.book') }}" class="space-y-3">
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
            <input type="hidden" name="appointment_slot_id" id="field-slot-id" value="{{ old('appointment_slot_id') }}">
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Prénom</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Nom</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Téléphone <span class="normal-case font-normal text-zinc-600">(optionnel)</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Message <span class="normal-case font-normal text-zinc-600">(optionnel)</span></label>
                <textarea name="message" rows="3" maxlength="2000" class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25">{{ old('message') }}</textarea>
            </div>
            <button type="submit" @if($previewMode) disabled @endif class="w-full rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                @if ($previewMode) Réservation désactivée (aperçu) @else Confirmer le rendez-vous @endif
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.__AGENDA__ = {
    slots: @json($slots),
    busy: @json($busy),
    mode: 'visitor',
    previewMode: @json($previewMode),
    oldSlotId: @json((string) old('appointment_slot_id')),
};
</script>
@include('agenda.partials.calendar-js')
<script>
(function () {
    const A = window.__AGENDA__;
    const modal = document.getElementById('modal-book');
    const chosen = document.getElementById('chosen-slot');
    const fieldId = document.getElementById('field-slot-id');

    window.agendaOnSlotClick = function (slot) {
        if (slot.busy) return;
        fieldId.value = slot.id;
        chosen.textContent = `${AgendaCal.prettyDay(slot.day)} · ${slot.start} – ${slot.end}`;
        AgendaCal.showModal('modal-book');
    };

    AgendaCal.init();

    if (A.oldSlotId) {
        const slot = A.slots.find(s => String(s.id) === A.oldSlotId);
        if (slot) {
            AgendaCal.goToDay(slot.day);
            window.agendaOnSlotClick(slot);
        }
    }
})();
</script>
@endpush
