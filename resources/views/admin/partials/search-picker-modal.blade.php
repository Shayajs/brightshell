@php
    $pickerId = $id ?? 'admin-search-picker';
    $pickerTitle = $title ?? 'Recherche';
    $pickerButtonLabel = $buttonLabel ?? 'Sélectionner';
    $pickerPrefilter = $prefilter ?? null;
    $pickerTypes = $types ?? [];
    $pickerSearchRoute = $searchRoute ?? route('admin.search.modal');
@endphp

<div id="{{ $pickerId }}" class="hidden fixed inset-0 z-[70]" aria-hidden="true">
    <div class="absolute inset-0 bg-black/70" data-picker-close></div>
    <div class="relative mx-auto mt-20 w-full max-w-2xl rounded-2xl border border-zinc-700 bg-zinc-900 shadow-2xl ring-1 ring-white/10">
        <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4">
            <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">{{ $pickerTitle }}</h3>
            <button type="button" class="rounded-md border border-zinc-700 px-2 py-1 text-xs text-zinc-400 hover:text-zinc-200" data-picker-close>Fermer</button>
        </div>
        <div class="space-y-4 px-5 py-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="{{ $pickerId }}-q" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Recherche</label>
                    <input id="{{ $pickerId }}-q" type="search" autocomplete="off"
                           class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30"
                           placeholder="Nom, e-mail, SIRET...">
                </div>
                <div>
                    <label for="{{ $pickerId }}-type" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Type</label>
                    <select id="{{ $pickerId }}-type"
                            class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 focus:border-indigo-500/50 focus:outline-none focus:ring-1 focus:ring-indigo-500/30">
                        <option value="">Auto</option>
                        <option value="user">Personnes</option>
                        <option value="company">Sociétés</option>
                        <option value="ticket">Tickets</option>
                    </select>
                </div>
            </div>
            <div id="{{ $pickerId }}-results" class="max-h-80 overflow-auto rounded-xl border border-zinc-800 bg-zinc-950/50">
                <p class="px-4 py-6 text-sm text-zinc-500">Tape au moins 2 caractères pour lancer la recherche.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const modal = document.getElementById(@json($pickerId));
    if (!modal) return;

    const qInput = modal.querySelector('#' + @json($pickerId . '-q'));
    const typeSelect = modal.querySelector('#' + @json($pickerId . '-type'));
    const results = modal.querySelector('#' + @json($pickerId . '-results'));
    const pickerId = @json($pickerId);
    const openers = document.querySelectorAll(`[data-picker-open="${pickerId}"]`);
    const hiddenInput = document.querySelector(`[data-picker-target="${pickerId}"]`);
    const submitButton = document.querySelector(`[data-picker-submit="${pickerId}"]`);
    const selectedLabel = document.querySelector(`[data-picker-selected="${pickerId}"]`);
    const endpoint = @json($pickerSearchRoute);
    const initialPrefilter = @json($pickerPrefilter);
    const initialTypes = @json($pickerTypes);
    const chooseLabel = @json($pickerButtonLabel);

    let selectedId = '';
    let selectedText = '';
    let timer = null;

    const close = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
    };
    const open = () => {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        qInput.focus();
    };

    modal.querySelectorAll('[data-picker-close]').forEach((el) => {
        el.addEventListener('click', close);
    });
    openers.forEach((btn) => btn.addEventListener('click', open));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });

    const updateSelection = (id, text) => {
        selectedId = String(id || '');
        selectedText = text || '';
        if (hiddenInput) hiddenInput.value = selectedId;
        if (selectedLabel) selectedLabel.textContent = selectedText || 'Aucune sélection';
        if (submitButton) submitButton.disabled = selectedId === '';
        close();
    };

    const renderItems = (items, key) => {
        if (!Array.isArray(items) || items.length === 0) return '';
        const title = key === 'users' ? 'Personnes' : (key === 'companies' ? 'Sociétés' : 'Tickets');
        const rows = items.map((item) => {
            const subtitle = item.subtitle ? `<p class="text-xs text-zinc-500">${item.subtitle}</p>` : '';
            const meta = item.meta ? `<span class="text-[10px] text-amber-300">${item.meta}</span>` : '';
            return `<button type="button" class="flex w-full items-center justify-between gap-3 border-b border-zinc-800 px-4 py-3 text-left hover:bg-zinc-800/40" data-id="${item.id}" data-label="${(item.label || '').replace(/"/g, '&quot;')}">
                <span><p class="text-sm font-medium text-zinc-100">${item.label || ''}</p>${subtitle}</span>${meta}
            </button>`;
        }).join('');
        return `<div><p class="px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-zinc-500">${title}</p>${rows}</div>`;
    };

    const fetchResults = async () => {
        const q = (qInput.value || '').trim();
        if (q.length < 2) {
            results.innerHTML = '<p class="px-4 py-6 text-sm text-zinc-500">Tape au moins 2 caractères pour lancer la recherche.</p>';
            return;
        }

        const params = new URLSearchParams();
        params.set('q', q);
        const typeValue = (typeSelect.value || '').trim();
        if (typeValue) {
            params.append('types[]', typeValue);
        } else if (Array.isArray(initialTypes) && initialTypes.length > 0) {
            initialTypes.forEach((type) => params.append('types[]', type));
        } else if (initialPrefilter) {
            params.set('prefilter', initialPrefilter);
        }

        results.innerHTML = '<p class="px-4 py-6 text-sm text-zinc-500">Recherche...</p>';

        const response = await fetch(`${endpoint}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            results.innerHTML = '<p class="px-4 py-6 text-sm text-red-400">Erreur pendant la recherche.</p>';
            return;
        }

        const payload = await response.json();
        const html = Object.entries(payload.results || {}).map(([key, items]) => renderItems(items, key)).join('');
        results.innerHTML = html || '<p class="px-4 py-6 text-sm text-zinc-500">Aucun résultat.</p>';

        results.querySelectorAll('button[data-id]').forEach((button) => {
            button.addEventListener('click', () => {
                updateSelection(button.getAttribute('data-id'), button.getAttribute('data-label'));
            });
        });
    };

    qInput.addEventListener('input', () => {
        if (timer) window.clearTimeout(timer);
        timer = window.setTimeout(fetchResults, 200);
    });
    typeSelect.addEventListener('change', fetchResults);

    if (submitButton) {
        submitButton.addEventListener('click', (event) => {
            if (!selectedId) {
                event.preventDefault();
                alert('Sélectionne un résultat dans la modale.');
            } else if (submitButton.textContent.trim() === '') {
                submitButton.textContent = chooseLabel;
            }
        });
    }
})();
</script>
@endpush
