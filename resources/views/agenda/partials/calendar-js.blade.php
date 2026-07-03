<script>
window.AgendaCal = (function () {
    const A = window.__AGENDA__;
    const MONTHS = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const DAYS = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
    const HH = 44; // px par heure

    const byDaySlots = {};
    (A.slots || []).forEach(s => { (byDaySlots[s.day] = byDaySlots[s.day] || []).push(s); });
    Object.values(byDaySlots).forEach(l => l.sort((a, b) => a.start.localeCompare(b.start)));

    const byDayBusy = {};
    (A.busy || []).forEach(b => { (byDayBusy[b.day] = byDayBusy[b.day] || []).push(b); });

    // Plage horaire dynamique
    let startH = 24, endH = 0;
    [...(A.slots || []), ...(A.busy || [])].forEach(e => {
        const sh = parseInt(e.start.split(':')[0], 10);
        const [eh, em] = e.end.split(':').map(Number);
        if (sh < startH) startH = sh;
        const ec = em > 0 ? eh + 1 : eh;
        if (ec > endH) endH = ec;
    });
    if (startH >= endH) { startH = 8; endH = 19; }
    startH = Math.max(0, startH); endH = Math.min(24, Math.max(endH, startH + 1));

    const pad = n => String(n).padStart(2, '0');
    const ymd = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const toMin = hm => { const [h, m] = hm.split(':').map(Number); return h * 60 + m; };
    const parseYmd = str => { const [y, m, d] = str.split('-').map(Number); return new Date(y, m - 1, d); };
    const prettyDay = str => parseYmd(str).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
    const mondayOf = d => { const x = new Date(d); const off = (x.getDay() + 6) % 7; x.setDate(x.getDate() - off); x.setHours(0, 0, 0, 0); return x; };

    const today = new Date(); today.setHours(0, 0, 0, 0);
    let mode = 'week';
    let anchor = new Date(today);

    let root, label, weekBtn, monthBtn;

    const noop = () => {};
    const onSlot = () => window.agendaOnSlotClick || noop;
    const onBusy = () => window.agendaOnBusyClick || noop;
    const onDayCreate = () => window.agendaOnDayCreate || noop;

    function styleToggle() {
        const on = 'bg-indigo-600 text-white';
        const off = 'text-zinc-400';
        weekBtn.className = 'rounded-md px-3 py-1.5 text-xs font-semibold transition ' + (mode === 'week' ? on : off);
        monthBtn.className = 'rounded-md px-3 py-1.5 text-xs font-semibold transition ' + (mode === 'month' ? on : off);
    }

    function render() {
        styleToggle();
        root.innerHTML = '';
        if (mode === 'week') renderWeek(); else renderMonth();
    }

    // ---- Vue semaine ----
    function renderWeek() {
        const monday = mondayOf(anchor);
        const days = [];
        for (let i = 0; i < 7; i++) { const d = new Date(monday); d.setDate(monday.getDate() + i); days.push(d); }
        label.textContent = `${days[0].getDate()} – ${days[6].getDate()} ${MONTHS[days[6].getMonth()]} ${days[6].getFullYear()}`;

        const tpl = '56px repeat(7, minmax(0, 1fr))';

        const header = document.createElement('div');
        header.className = 'grid border-b border-zinc-800';
        header.style.gridTemplateColumns = tpl;
        header.appendChild(document.createElement('div'));
        days.forEach(d => {
            const cell = document.createElement('div');
            const isToday = ymd(d) === ymd(today);
            cell.className = 'px-1 py-2 text-center';
            cell.innerHTML = `<span class="text-[11px] font-semibold uppercase ${isToday ? 'text-indigo-300' : 'text-zinc-500'}">${DAYS[(d.getDay()+6)%7]}</span>`
                + `<div class="mx-auto mt-1 flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold ${isToday ? 'bg-indigo-600 text-white' : 'text-zinc-200'}">${d.getDate()}</div>`;
            header.appendChild(cell);
        });
        root.appendChild(header);

        const body = document.createElement('div');
        body.className = 'grid overflow-y-auto';
        body.style.gridTemplateColumns = tpl;
        body.style.maxHeight = '64vh';

        // Colonne des heures
        const timeCol = document.createElement('div');
        timeCol.style.position = 'relative';
        timeCol.style.height = ((endH - startH) * HH) + 'px';
        for (let h = startH; h <= endH; h++) {
            const t = document.createElement('div');
            t.className = 'absolute right-1 -translate-y-1/2 text-[10px] text-zinc-600';
            t.style.top = ((h - startH) * HH) + 'px';
            t.textContent = pad(h) + ':00';
            timeCol.appendChild(t);
        }
        body.appendChild(timeCol);

        days.forEach(d => body.appendChild(dayColumn(d)));
        root.appendChild(body);
    }

    function dayColumn(dateObj) {
        const dayStr = ymd(dateObj);
        const col = document.createElement('div');
        col.className = 'relative border-l border-zinc-800/70';
        col.style.height = ((endH - startH) * HH) + 'px';

        for (let h = startH; h <= endH; h++) {
            const line = document.createElement('div');
            line.className = 'absolute inset-x-0 border-t border-zinc-800/50';
            line.style.top = ((h - startH) * HH) + 'px';
            col.appendChild(line);
        }

        // Blocs occupés (arrière-plan)
        (byDayBusy[dayStr] || []).forEach(block => {
            const el = document.createElement('div');
            const top = Math.max(0, (toMin(block.start) - startH * 60) / 60 * HH);
            const h = Math.max(16, (toMin(block.end) - toMin(block.start)) / 60 * HH);
            el.style.position = 'absolute';
            el.style.top = top + 'px';
            el.style.height = h + 'px';
            el.style.left = '2px'; el.style.right = '2px';
            el.className = 'z-0 overflow-hidden rounded-md border border-zinc-700 bg-zinc-800/70 px-1 py-0.5 text-[10px] text-zinc-400';
            el.style.backgroundImage = 'repeating-linear-gradient(45deg, rgba(255,255,255,0.04) 0 6px, transparent 6px 12px)';
            el.textContent = block.title ? ('Occupé · ' + block.title) : 'Occupé';
            if (A.mode === 'admin') {
                el.style.cursor = 'pointer';
                el.title = 'Gérer cette indisponibilité';
                el.addEventListener('click', () => onBusy()(block));
            }
            col.appendChild(el);
        });

        // Créneaux (avant-plan)
        (byDaySlots[dayStr] || []).forEach(slot => {
            const el = document.createElement('button');
            el.type = 'button';
            const top = Math.max(0, (toMin(slot.start) - startH * 60) / 60 * HH);
            const h = Math.max(20, (toMin(slot.end) - toMin(slot.start)) / 60 * HH);
            el.style.position = 'absolute';
            el.style.top = top + 'px';
            el.style.height = h + 'px';
            el.style.left = '3px'; el.style.right = '3px';
            el.className = 'z-10 overflow-hidden rounded-md px-1.5 py-0.5 text-left text-[11px] font-semibold ' + slotClass(slot);
            el.innerHTML = `<span class="block truncate">${slot.start} – ${slot.end}</span>` + slotSub(slot);
            el.addEventListener('click', () => onSlot()(slot));
            col.appendChild(el);
        });

        return col;
    }

    function slotClass(slot) {
        if (A.mode === 'admin') {
            if (slot.status === 'booked') return 'border border-indigo-500/40 bg-indigo-600/80 text-white';
            if (slot.status === 'blocked') return 'border border-zinc-600 bg-zinc-800 text-zinc-400';
            if (slot.busy) return 'border border-amber-500/40 bg-amber-500/10 text-amber-200';
            return 'border border-emerald-500/40 bg-emerald-600/25 text-emerald-100 hover:bg-emerald-600/40';
        }
        // visiteur
        if (slot.busy) return 'cursor-not-allowed border border-zinc-700 bg-zinc-800/80 text-zinc-500 line-through';
        return 'border border-indigo-500/40 bg-indigo-600/80 text-white hover:bg-indigo-500';
    }

    function slotSub(slot) {
        if (A.mode === 'admin' && slot.booking) return `<span class="block truncate text-[10px] font-normal opacity-90">${slot.booking.name}</span>`;
        if (A.mode === 'visitor' && slot.busy) return `<span class="block text-[10px] font-normal">Occupé</span>`;
        return '';
    }

    // ---- Vue mois ----
    function renderMonth() {
        anchor.setDate(1);
        label.textContent = `${MONTHS[anchor.getMonth()]} ${anchor.getFullYear()}`;

        const headRow = document.createElement('div');
        headRow.className = 'mb-1 grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wide text-zinc-500';
        DAYS.forEach(d => { const s = document.createElement('span'); s.textContent = d; headRow.appendChild(s); });
        root.appendChild(headRow);

        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-7 gap-1';

        const first = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
        const offset = (first.getDay() + 6) % 7;
        const daysInMonth = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 0).getDate();
        for (let i = 0; i < offset; i++) grid.appendChild(document.createElement('div'));

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(anchor.getFullYear(), anchor.getMonth(), day);
            const key = ymd(date);
            const slots = byDaySlots[key] || [];
            const isPast = date < today;

            const cell = document.createElement('div');
            cell.className = 'flex min-h-20 cursor-pointer flex-col rounded-xl border border-zinc-800 bg-zinc-950/40 p-1.5 transition hover:border-indigo-500/50';
            if (key === ymd(today)) cell.classList.add('ring-1', 'ring-inset', 'ring-indigo-400/40');
            cell.addEventListener('click', () => goToDay(key));

            const num = document.createElement('div');
            num.className = 'mb-1 px-1 text-xs font-semibold ' + (isPast ? 'text-zinc-600' : 'text-zinc-400');
            num.textContent = day;
            cell.appendChild(num);

            if (A.mode === 'visitor') {
                const avail = slots.filter(s => !s.busy).length;
                if (avail > 0 && !isPast) {
                    const b = document.createElement('span');
                    b.className = 'mx-1 rounded-md bg-indigo-500/15 px-1.5 py-0.5 text-center text-[11px] font-semibold text-indigo-300';
                    b.textContent = avail + (avail > 1 ? ' dispos' : ' dispo');
                    cell.appendChild(b);
                }
            } else {
                slots.slice(0, 3).forEach(slot => {
                    const meta = slot.status === 'booked' ? 'bg-indigo-500/15 text-indigo-200'
                        : slot.status === 'blocked' ? 'bg-zinc-800 text-zinc-400'
                        : slot.busy ? 'bg-amber-500/10 text-amber-200'
                        : 'bg-emerald-500/15 text-emerald-200';
                    const chip = document.createElement('span');
                    chip.className = 'mb-0.5 truncate rounded px-1 text-[10px] font-semibold ' + meta;
                    chip.textContent = slot.start + (slot.booking ? ' ' + slot.booking.name : '');
                    cell.appendChild(chip);
                });
                if (slots.length > 3) {
                    const more = document.createElement('span');
                    more.className = 'px-1 text-[10px] text-zinc-500';
                    more.textContent = '+' + (slots.length - 3);
                    cell.appendChild(more);
                }
                if ((byDayBusy[key] || []).length) {
                    const bb = document.createElement('span');
                    bb.className = 'mt-0.5 truncate rounded bg-zinc-800/80 px-1 text-[10px] text-zinc-400';
                    bb.textContent = 'Occupé';
                    cell.appendChild(bb);
                }
            }

            grid.appendChild(cell);
        }
        root.appendChild(grid);
    }

    function goToDay(dayStr) {
        anchor = parseYmd(dayStr);
        mode = 'week';
        render();
    }

    function step(dir) {
        if (mode === 'week') { anchor.setDate(anchor.getDate() + dir * 7); }
        else { anchor = new Date(anchor.getFullYear(), anchor.getMonth() + dir, 1); }
        render();
    }

    // ---- Modales génériques ----
    function showModal(id) { const m = document.getElementById(id); m.classList.remove('hidden'); m.classList.add('flex'); }
    function hideModal(m) { m.classList.add('hidden'); m.classList.remove('flex'); }

    function init() {
        root = document.getElementById('cal-root');
        label = document.getElementById('cal-label');
        weekBtn = document.getElementById('cal-week-btn');
        monthBtn = document.getElementById('cal-month-btn');

        document.getElementById('cal-prev').addEventListener('click', () => step(-1));
        document.getElementById('cal-next').addEventListener('click', () => step(1));
        document.getElementById('cal-today').addEventListener('click', () => { anchor = new Date(today); render(); });
        weekBtn.addEventListener('click', () => { mode = 'week'; render(); });
        monthBtn.addEventListener('click', () => { mode = 'month'; anchor.setDate(1); render(); });

        document.querySelectorAll('.modal-close').forEach(b => b.addEventListener('click', () => hideModal(b.closest('.fixed'))));
        document.querySelectorAll('.fixed.inset-0').forEach(m => m.addEventListener('click', e => { if (e.target === m) hideModal(m); }));

        render();
    }

    return { init, render, goToDay, showModal, prettyDay, ymd: (d) => ymd(d) };
})();
</script>
