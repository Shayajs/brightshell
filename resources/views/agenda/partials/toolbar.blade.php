<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <button type="button" id="cal-prev" class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-700 text-zinc-400 transition hover:border-indigo-500/50 hover:text-indigo-300" aria-label="Précédent">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <button type="button" id="cal-next" class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-700 text-zinc-400 transition hover:border-indigo-500/50 hover:text-indigo-300" aria-label="Suivant">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <button type="button" id="cal-today" class="rounded-lg border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:border-indigo-500/50 hover:text-indigo-300">Aujourd'hui</button>
        <p id="cal-label" class="ml-2 font-display text-lg font-bold capitalize text-white"></p>
    </div>

    <div class="inline-flex rounded-lg border border-zinc-700 p-0.5">
        <button type="button" id="cal-week-btn" class="rounded-md px-3 py-1.5 text-xs font-semibold text-zinc-400 transition">Semaine</button>
        <button type="button" id="cal-month-btn" class="rounded-md px-3 py-1.5 text-xs font-semibold text-zinc-400 transition">Mois</button>
    </div>
</div>
