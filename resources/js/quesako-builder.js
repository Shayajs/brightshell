const root = document.getElementById('quesako-builder');

if (root) {
    const tabsList = document.getElementById('tabs-list');
    const modulesList = document.getElementById('modules-list');
    const hiddenInput = document.getElementById('quesako-config-input');
    const addTabBtn = document.getElementById('add-tab-btn');
    const addModuleBtn = document.getElementById('add-module-btn');
    const seoTitle = document.getElementById('seo-title');
    const seoDescription = document.getElementById('seo-description');
    const previewFrame = document.getElementById('quesako-preview-frame');
    const form = document.getElementById('quesako-builder-form');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const initial = JSON.parse(root.dataset.initialConfig || '{}');
    const allowedModules = JSON.parse(root.dataset.allowedModules || '[]');
    const previewUrl = root.dataset.previewUrl || '';

    const state = {
        config: initial,
        activeTabSlug: initial?.settings?.defaultTabSlug || (initial?.tabs?.[0]?.slug ?? 'about'),
    };

    const slugify = (value) => String(value || '').toLowerCase().trim().replace(/[^a-z0-9-]+/g, '-').replace(/(^-|-$)/g, '').slice(0, 40);
    const uid = () => Math.random().toString(16).slice(2, 10);

    const ensureShape = () => {
        state.config.tabs = Array.isArray(state.config.tabs) ? state.config.tabs : [];
        state.config.modulesByTab = state.config.modulesByTab && typeof state.config.modulesByTab === 'object' ? state.config.modulesByTab : {};
        state.config.settings = state.config.settings && typeof state.config.settings === 'object' ? state.config.settings : {};
        if (!state.config.settings.defaultTabSlug && state.config.tabs[0]) {
            state.config.settings.defaultTabSlug = state.config.tabs[0].slug;
        }
    };

    const parseLines = (value) => String(value || '').split('\n').map(v => v.trim()).filter(Boolean);
    const parseTimeline = (value) => parseLines(value).map((line) => {
        const [label, ...rest] = line.split('|');
        return { label: (label || '').trim(), text: rest.join('|').trim() };
    });
    const parsePairs = (value, leftKey, rightKey) => parseLines(value).map((line) => {
        const [left, ...rest] = line.split('|');
        return { [leftKey]: (left || '').trim(), [rightKey]: rest.join('|').trim() };
    });
    const moduleOptions = () => allowedModules.map((type) => `<option value="${type}">${type}</option>`).join('');

    const moduleEditorTemplate = (module) => {
        const props = module.props || {};
        if (module.type === 'hero') {
            return `
                <label class="block text-xs text-zinc-400">Titre hero
                    <input data-prop="headline" type="text" value="${props.headline || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Sous-titre
                    <textarea data-prop="subheadline" rows="2" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">${props.subheadline || ''}</textarea>
                </label>
                <label class="block text-xs text-zinc-400">Animation
                    <select data-prop="animationVariant" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                        <option value="fade-up" ${props.animationVariant === 'fade-up' ? 'selected' : ''}>Fade Up</option>
                        <option value="fade-in" ${props.animationVariant === 'fade-in' ? 'selected' : ''}>Fade In</option>
                        <option value="slide-right" ${props.animationVariant === 'slide-right' ? 'selected' : ''}>Slide Right</option>
                    </select>
                </label>
            `;
        }
        if (module.type === 'services') {
            return `
                <label class="block text-xs text-zinc-400">Titre
                    <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Elements (une ligne = une offre)
                    <textarea data-prop="items" rows="4" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 font-mono text-sm text-zinc-100">${(props.items || []).join('\n')}</textarea>
                </label>
            `;
        }
        if (module.type === 'timeline') {
            const val = (props.steps || []).map((s) => `${s.label || ''} | ${s.text || ''}`).join('\n');
            return `
                <label class="block text-xs text-zinc-400">Titre
                    <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Etapes (Label | Texte)
                    <textarea data-prop="steps" rows="4" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 font-mono text-sm text-zinc-100">${val}</textarea>
                </label>
            `;
        }
        if (module.type === 'quote') {
            return `
                <label class="block text-xs text-zinc-400">Citation
                    <textarea data-prop="quote" rows="3" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">${props.quote || ''}</textarea>
                </label>
                <label class="block text-xs text-zinc-400">Auteur
                    <input data-prop="author" type="text" value="${props.author || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
            `;
        }
        if (module.type === 'cta') {
            return `
                <label class="block text-xs text-zinc-400">Titre
                    <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Texte
                    <textarea data-prop="body" rows="2" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">${props.body || ''}</textarea>
                </label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="block text-xs text-zinc-400">Label bouton
                        <input data-prop="buttonLabel" type="text" value="${props.buttonLabel || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                    </label>
                    <label class="block text-xs text-zinc-400">URL bouton
                        <input data-prop="buttonUrl" type="text" value="${props.buttonUrl || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                    </label>
                </div>
            `;
        }
        if (module.type === 'stats') {
            return `
                <label class="block text-xs text-zinc-400">Titre
                    <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Stats (Label | Valeur)
                    <textarea data-prop="items" rows="4" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 font-mono text-sm text-zinc-100">${(props.items || []).map((i) => `${i.label || ''} | ${i.value || ''}`).join('\n')}</textarea>
                </label>
            `;
        }
        if (module.type === 'cards') {
            return `
                <label class="block text-xs text-zinc-400">Titre
                    <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Cartes (Titre | Texte)
                    <textarea data-prop="cards" rows="4" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 font-mono text-sm text-zinc-100">${(props.cards || []).map((c) => `${c.title || ''} | ${c.text || ''}`).join('\n')}</textarea>
                </label>
            `;
        }
        if (module.type === 'faq') {
            return `
                <label class="block text-xs text-zinc-400">Titre
                    <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Questions (Question | Reponse)
                    <textarea data-prop="items" rows="5" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 font-mono text-sm text-zinc-100">${(props.items || []).map((i) => `${i.question || ''} | ${i.answer || ''}`).join('\n')}</textarea>
                </label>
            `;
        }
        if (module.type === 'media') {
            return `
                <label class="block text-xs text-zinc-400">Titre
                    <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Image URL
                    <input data-prop="imageUrl" type="text" value="${props.imageUrl || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
                <label class="block text-xs text-zinc-400">Legende
                    <input data-prop="caption" type="text" value="${props.caption || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
            `;
        }
        if (module.type === 'divider') {
            return `
                <label class="block text-xs text-zinc-400">Label (optionnel)
                    <input data-prop="label" type="text" value="${props.label || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                </label>
            `;
        }
        return `
            <label class="block text-xs text-zinc-400">Titre
                <input data-prop="title" type="text" value="${props.title || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
            </label>
            <label class="block text-xs text-zinc-400">Texte
                <textarea data-prop="body" rows="3" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">${props.body || ''}</textarea>
            </label>
        `;
    };

    const syncHiddenInput = () => {
        hiddenInput.value = JSON.stringify(state.config);
    };

    const getModules = () => {
        const slug = state.activeTabSlug;
        state.config.modulesByTab[slug] = Array.isArray(state.config.modulesByTab[slug]) ? state.config.modulesByTab[slug] : [];
        return state.config.modulesByTab[slug];
    };

    const renderTabs = () => {
        tabsList.innerHTML = '';
        const tabs = state.config.tabs;

        tabs.forEach((tab, idx) => {
            const item = document.createElement('div');
            item.className = 'rounded-xl border border-zinc-800 bg-zinc-950/50 p-3';
            item.innerHTML = `
                <div class="grid gap-2 sm:grid-cols-12">
                    <label class="sm:col-span-4 text-xs text-zinc-400">Libelle
                        <input data-k="label" value="${tab.label || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">
                    </label>
                    <label class="sm:col-span-3 text-xs text-zinc-400">Slug
                        <input data-k="slug" value="${tab.slug || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 font-mono text-sm text-zinc-100">
                    </label>
                    <label class="sm:col-span-2 text-xs text-zinc-400">Actif
                        <input data-k="enabled" type="checkbox" ${tab.enabled ? 'checked' : ''} class="mt-2 block">
                    </label>
                    <label class="sm:col-span-2 text-xs text-zinc-400">Selection
                        <input data-k="selected" type="radio" name="active-tab" ${state.activeTabSlug === tab.slug ? 'checked' : ''} class="mt-2 block">
                    </label>
                    <div class="sm:col-span-1 flex items-end">
                        <button type="button" data-k="remove" class="rounded border border-red-700 px-2 py-1 text-xs text-red-300 hover:bg-red-900/20">X</button>
                    </div>
                </div>
            `;

            item.querySelector('[data-k="label"]').addEventListener('input', (e) => {
                tab.label = e.target.value;
                syncAndPreview();
            });
            item.querySelector('[data-k="slug"]').addEventListener('change', (e) => {
                const oldSlug = tab.slug;
                const next = slugify(e.target.value) || `tab-${idx + 1}`;
                tab.slug = next;
                if (oldSlug !== next) {
                    state.config.modulesByTab[next] = state.config.modulesByTab[oldSlug] || [];
                    delete state.config.modulesByTab[oldSlug];
                    if (state.activeTabSlug === oldSlug) state.activeTabSlug = next;
                    if (state.config.settings.defaultTabSlug === oldSlug) state.config.settings.defaultTabSlug = next;
                }
                renderAll();
            });
            item.querySelector('[data-k="enabled"]').addEventListener('change', (e) => {
                tab.enabled = !!e.target.checked;
                syncAndPreview();
            });
            item.querySelector('[data-k="selected"]').addEventListener('change', () => {
                state.activeTabSlug = tab.slug;
                state.config.settings.defaultTabSlug = tab.slug;
                renderModules();
                syncAndPreview();
            });
            item.querySelector('[data-k="remove"]').addEventListener('click', () => {
                if (tabs.length <= 1) return;
                tabs.splice(idx, 1);
                delete state.config.modulesByTab[tab.slug];
                if (state.activeTabSlug === tab.slug) {
                    state.activeTabSlug = tabs[0]?.slug || '';
                }
                if (state.config.settings.defaultTabSlug === tab.slug) {
                    state.config.settings.defaultTabSlug = state.activeTabSlug;
                }
                renderAll();
            });

            tabsList.appendChild(item);
        });
    };

    const renderModules = () => {
        modulesList.innerHTML = '';
        const mods = getModules();
        mods.forEach((module, idx) => {
            const item = document.createElement('div');
            item.className = 'rounded-xl border border-zinc-800 bg-zinc-950/50 p-3';
            item.innerHTML = `
                <div class="mb-2 grid gap-2 sm:grid-cols-12">
                    <label class="sm:col-span-3 text-xs text-zinc-400">Type
                        <select data-k="type" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100">${moduleOptions()}</select>
                    </label>
                    <label class="sm:col-span-3 text-xs text-zinc-400">Nom interne
                        <input data-k="adminLabel" type="text" value="${module.adminLabel || ''}" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-sm text-zinc-100" placeholder="Ex: Hero principal">
                    </label>
                    <label class="sm:col-span-2 text-xs text-zinc-400">Actif
                        <input data-k="enabled" type="checkbox" ${module.enabled ? 'checked' : ''} class="mt-2 block">
                    </label>
                    <label class="sm:col-span-2 text-xs text-zinc-400">Vers onglet
                        <select data-k="moveTab" class="mt-1 w-full rounded border border-zinc-700 bg-zinc-950 px-2 py-1.5 text-xs text-zinc-100">
                            ${state.config.tabs.map((t) => `<option value="${t.slug}" ${t.slug === state.activeTabSlug ? 'selected' : ''}>${t.label || t.slug}</option>`).join('')}
                        </select>
                    </label>
                    <div class="sm:col-span-2 flex items-end justify-end gap-2">
                        <button type="button" data-k="duplicate" class="rounded border border-zinc-700 px-2 py-1 text-xs text-zinc-300">Dupliquer</button>
                        <button type="button" data-k="up" class="rounded border border-zinc-700 px-2 py-1 text-xs text-zinc-300">↑</button>
                        <button type="button" data-k="down" class="rounded border border-zinc-700 px-2 py-1 text-xs text-zinc-300">↓</button>
                        <button type="button" data-k="remove" class="rounded border border-red-700 px-2 py-1 text-xs text-red-300">Suppr</button>
                    </div>
                </div>
                <div data-module-editor class="space-y-2">${moduleEditorTemplate(module)}</div>
            `;
            const typeSelect = item.querySelector('[data-k="type"]');
            typeSelect.value = module.type;

            typeSelect.addEventListener('change', (e) => {
                module.type = e.target.value;
                module.props = {};
                renderModules();
                syncAndPreview();
            });
            item.querySelector('[data-k="adminLabel"]').addEventListener('input', (e) => {
                module.adminLabel = e.target.value;
                syncAndPreview();
            });
            item.querySelector('[data-k="enabled"]').addEventListener('change', (e) => {
                module.enabled = !!e.target.checked;
                syncAndPreview();
            });
            item.querySelector('[data-k="duplicate"]').addEventListener('click', () => {
                const copy = JSON.parse(JSON.stringify(module));
                copy.id = `mod-${uid()}`;
                copy.adminLabel = copy.adminLabel ? `${copy.adminLabel} (copie)` : '';
                mods.splice(idx + 1, 0, copy);
                renderModules();
                syncAndPreview();
            });
            item.querySelector('[data-k="moveTab"]').addEventListener('change', (e) => {
                const to = e.target.value;
                if (!to || to === state.activeTabSlug) return;
                state.config.modulesByTab[to] = Array.isArray(state.config.modulesByTab[to]) ? state.config.modulesByTab[to] : [];
                state.config.modulesByTab[to].push(module);
                mods.splice(idx, 1);
                renderModules();
                syncAndPreview();
            });
            item.querySelector('[data-k="remove"]').addEventListener('click', () => {
                mods.splice(idx, 1);
                renderModules();
                syncAndPreview();
            });
            item.querySelector('[data-k="up"]').addEventListener('click', () => {
                if (idx < 1) return;
                [mods[idx - 1], mods[idx]] = [mods[idx], mods[idx - 1]];
                renderModules();
                syncAndPreview();
            });
            item.querySelector('[data-k="down"]').addEventListener('click', () => {
                if (idx >= mods.length - 1) return;
                [mods[idx + 1], mods[idx]] = [mods[idx], mods[idx + 1]];
                renderModules();
                syncAndPreview();
            });

            item.querySelectorAll('[data-prop]').forEach((input) => {
                input.addEventListener('input', (e) => {
                    const key = e.target.dataset.prop;
                    module.props = module.props || {};
                    if (module.type === 'services' && key === 'items') {
                        module.props.items = parseLines(e.target.value);
                    } else if (module.type === 'timeline' && key === 'steps') {
                        module.props.steps = parseTimeline(e.target.value);
                    } else if (module.type === 'stats' && key === 'items') {
                        module.props.items = parsePairs(e.target.value, 'label', 'value');
                    } else if (module.type === 'cards' && key === 'cards') {
                        module.props.cards = parsePairs(e.target.value, 'title', 'text');
                    } else if (module.type === 'faq' && key === 'items') {
                        module.props.items = parsePairs(e.target.value, 'question', 'answer');
                    } else {
                        module.props[key] = e.target.value;
                    }
                    syncAndPreview();
                });
            });

            modulesList.appendChild(item);
        });
    };

    const renderAll = () => {
        ensureShape();
        seoTitle.value = state.config.settings.seoTitle || '';
        seoDescription.value = state.config.settings.seoDescription || '';
        renderTabs();
        renderModules();
        syncAndPreview();
    };

    let previewTimer = null;
    const renderPreview = async () => {
        if (!previewUrl || !previewFrame) return;
        try {
            const res = await fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    quesako_config: JSON.stringify(state.config),
                    tab_slug: state.activeTabSlug,
                }),
            });
            if (!res.ok) return;
            const payload = await res.json();
            if (payload?.html) {
                previewFrame.srcdoc = payload.html;
            }
        } catch {
            // no-op preview
        }
    };

    const syncAndPreview = () => {
        syncHiddenInput();
        clearTimeout(previewTimer);
        previewTimer = setTimeout(renderPreview, 220);
    };

    addTabBtn?.addEventListener('click', () => {
        const nextSlug = `tab-${state.config.tabs.length + 1}-${uid().slice(0, 3)}`;
        const tab = { id: `tab-${uid()}`, slug: nextSlug, label: 'Nouvel onglet', enabled: true, order: state.config.tabs.length + 1 };
        state.config.tabs.push(tab);
        state.config.modulesByTab[nextSlug] = [];
        state.activeTabSlug = nextSlug;
        state.config.settings.defaultTabSlug = nextSlug;
        renderAll();
    });

    addModuleBtn?.addEventListener('click', () => {
        const mods = getModules();
        const defaultType = allowedModules.includes('text') ? 'text' : (allowedModules[0] || 'about');
        mods.push({ id: `mod-${uid()}`, type: defaultType, adminLabel: '', enabled: true, order: mods.length + 1, props: { title: '', body: '' } });
        renderModules();
        syncAndPreview();
    });

    seoTitle?.addEventListener('input', (e) => {
        state.config.settings.seoTitle = e.target.value;
        syncAndPreview();
    });
    seoDescription?.addEventListener('input', (e) => {
        state.config.settings.seoDescription = e.target.value;
        syncAndPreview();
    });

    form?.addEventListener('submit', () => {
        syncHiddenInput();
    });

    renderAll();
}
