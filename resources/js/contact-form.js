/**
 * Page contact :
 * - bascule entre les 4 types (Curiosité / Pro / Réclamation / Projet)
 * - applique `auth-shell__panel--full` au panel quand "Soumettre un projet" est sélectionné
 * - met à jour ?type=... dans l'URL via history.replaceState
 * - éditeur Markdown live preview (mode projet)
 * - drag & drop pièces jointes (mode projet)
 */

const PROJECT_TYPE = 'project';
const PROJECT_PANEL_CLASS = 'auth-shell__panel--full';
const SUBTITLES = {
    general: 'Une question, une envie d’en savoir plus ? Écrivez-moi quelques lignes.',
    professional: 'Une opportunité, un partenariat, une mission : présentez votre besoin.',
    complaint: 'Un souci, un point bloquant ? Expliquez ce qui s’est passé, je reviens vers vous.',
    project: 'Décrivez votre projet en profondeur : Markdown, pièces jointes, budget, délais.',
};

function init() {
    const root = document.querySelector('[data-contact-root]');
    if (!root) return;

    const panel = document.querySelector('.auth-shell__panel');
    const triggers = Array.from(root.querySelectorAll('[data-contact-type-trigger]'));
    const panels = Array.from(root.querySelectorAll('[data-contact-panel]'));
    const subtitleEl = document.querySelector('[data-contact-subtitle]');

    const setType = (type, options = {}) => {
        const { syncUrl = true, focusFirst = false } = options;
        if (!SUBTITLES[type]) return;

        root.dataset.contactActiveType = type;

        for (const t of triggers) {
            t.setAttribute('aria-selected', t.dataset.contactTypeTrigger === type ? 'true' : 'false');
        }

        for (const p of panels) {
            p.hidden = p.dataset.contactPanel !== type;
        }

        if (panel) {
            if (type === PROJECT_TYPE) {
                panel.classList.add(PROJECT_PANEL_CLASS);
            } else {
                panel.classList.remove(PROJECT_PANEL_CLASS);
            }
        }

        if (subtitleEl) {
            subtitleEl.textContent = SUBTITLES[type];
        }

        if (syncUrl && typeof window.history?.replaceState === 'function') {
            const url = new URL(window.location.href);
            url.searchParams.set('type', type);
            window.history.replaceState(null, '', url.toString());
        }

        if (focusFirst) {
            const activePanel = panels.find((p) => p.dataset.contactPanel === type);
            const firstField = activePanel?.querySelector('input:not([type="hidden"]):not([class*="honeypot"]), textarea, select');
            if (firstField && typeof firstField.focus === 'function') {
                window.requestAnimationFrame(() => firstField.focus({ preventScroll: false }));
            }
        }
    };

    for (const trigger of triggers) {
        trigger.addEventListener('click', () => setType(trigger.dataset.contactTypeTrigger, { focusFirst: true }));
    }

    setType(root.dataset.contactActiveType || 'general', { syncUrl: false });

    initMarkdownEditor(root);
    initDropzone(root);
}

function initMarkdownEditor(root) {
    const previewUrl = root.dataset.contactPreviewUrl;
    const csrf = root.dataset.contactCsrf;
    const source = root.querySelector('[data-contact-md-source]');
    const preview = root.querySelector('[data-contact-md-preview]');
    const tabs = Array.from(root.querySelectorAll('[data-contact-md-tab]'));
    if (!source || !preview || !tabs.length) return;

    let timer = null;
    let lastRendered = null;

    const setActiveTab = (mode) => {
        for (const tab of tabs) {
            tab.setAttribute('aria-selected', tab.dataset.contactMdTab === mode ? 'true' : 'false');
        }
        if (mode === 'preview') {
            source.hidden = true;
            preview.hidden = false;
            renderNow();
        } else {
            preview.hidden = true;
            source.hidden = false;
            window.requestAnimationFrame(() => source.focus({ preventScroll: true }));
        }
    };

    const renderNow = async () => {
        const body = source.value || '';
        if (body === lastRendered) return;
        lastRendered = body;

        if (body.trim() === '') {
            preview.innerHTML = '<p class="contact-md-preview__placeholder">Commencez à écrire pour voir un aperçu.</p>';
            return;
        }

        try {
            const response = await fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: new URLSearchParams({ body }).toString(),
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('preview_failed');
            const data = await response.json();
            preview.innerHTML = data.html || '<p class="contact-md-preview__placeholder">Aperçu vide.</p>';
        } catch (err) {
            preview.innerHTML = '<p class="contact-md-preview__placeholder">Aperçu indisponible (réseau).</p>';
        }
    };

    for (const tab of tabs) {
        tab.addEventListener('click', () => setActiveTab(tab.dataset.contactMdTab));
    }

    source.addEventListener('input', () => {
        if (preview.hidden) return;
        if (timer) window.clearTimeout(timer);
        timer = window.setTimeout(renderNow, 300);
    });
}

function initDropzone(root) {
    const dropzone = root.querySelector('[data-contact-dropzone]');
    if (!dropzone) return;

    const input = dropzone.querySelector('[data-contact-files]');
    const list = dropzone.querySelector('[data-contact-files-list]');
    if (!input || !list) return;

    const renderList = () => {
        list.innerHTML = '';
        const files = Array.from(input.files || []);
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const li = document.createElement('li');
            li.className = 'contact-dropzone__item';

            const name = document.createElement('span');
            name.className = 'contact-dropzone__item-name';
            name.textContent = file.name;
            li.appendChild(name);

            const size = document.createElement('span');
            size.className = 'contact-dropzone__item-size';
            size.textContent = humanSize(file.size);
            li.appendChild(size);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'contact-dropzone__remove';
            remove.setAttribute('aria-label', `Retirer ${file.name}`);
            remove.textContent = '×';
            remove.addEventListener('click', () => removeFile(i));
            li.appendChild(remove);

            list.appendChild(li);
        }
    };

    const removeFile = (index) => {
        const dt = new DataTransfer();
        const files = Array.from(input.files || []);
        for (let i = 0; i < files.length; i++) {
            if (i === index) continue;
            dt.items.add(files[i]);
        }
        input.files = dt.files;
        renderList();
    };

    input.addEventListener('change', renderList);

    ['dragenter', 'dragover'].forEach((evt) => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('contact-dropzone--over');
        });
    });

    ['dragleave', 'drop'].forEach((evt) => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('contact-dropzone--over');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        const dropped = Array.from(e.dataTransfer?.files || []);
        if (!dropped.length) return;

        const dt = new DataTransfer();
        for (const f of Array.from(input.files || [])) dt.items.add(f);
        for (const f of dropped) dt.items.add(f);
        input.files = dt.files;
        renderList();
    });

    renderList();
}

function humanSize(bytes) {
    bytes = Number(bytes) || 0;
    if (bytes < 1024) return `${bytes} o`;
    const units = ['Ko', 'Mo', 'Go'];
    let i = -1;
    let value = bytes;
    do {
        value /= 1024;
        i++;
    } while (value >= 1024 && i < units.length - 1);
    return `${value >= 10 ? value.toFixed(0) : value.toFixed(1)} ${units[i]}`.replace('.', ',');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
} else {
    init();
}
