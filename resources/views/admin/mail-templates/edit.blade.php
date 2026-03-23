@extends('layouts.admin')

@section('title', 'Edition template mail')
@section('topbar_label', 'Templates mail')

@section('content')
    <div class="space-y-8" id="mail-template-editor"
         data-template-key="{{ $template['key'] }}"
         data-update-url="{{ route('admin.mail-templates.update', $template['key']) }}"
         data-preview-url="{{ route('admin.mail-templates.preview', $template['key']) }}">
        <header class="space-y-2">
            <a href="{{ route('admin.mail-templates.index') }}" class="text-xs font-semibold text-indigo-300 hover:text-indigo-200">← Retour liste</a>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $template['name'] }}</h1>
            <p class="text-sm text-zinc-400">{{ $template['key'] }} · version <span id="template-version">{{ $template['version'] }}</span></p>
        </header>

        @include('layouts.partials.flash')

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white">Edition JSON</h2>
                <form id="template-form" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-xs text-zinc-400">Nom</label>
                        <input type="text" name="name" value="{{ $template['name'] }}" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs text-zinc-400">Categorie</label>
                            <input type="text" name="category" value="{{ $template['category'] }}" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-zinc-300">
                                <input type="checkbox" name="is_active" value="1" @checked($template['is_active']) class="rounded border-zinc-600 bg-zinc-800 text-indigo-500">
                                Actif
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-400">Sujet (placeholders autorises)</label>
                        <input type="text" name="subject_template" value="{{ $template['subject_template'] }}" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-400">Layout JSON</label>
                        <textarea name="layout_json" rows="10" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-100">{{ json_encode($template['layout_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-400">Content JSON</label>
                        <textarea name="content_json" rows="16" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-100">{{ json_encode($template['content_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-zinc-400">Variables JSON</label>
                        <textarea name="variables_json" rows="6" class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 font-mono text-xs text-zinc-100">{{ json_encode($template['variables_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" id="btn-save" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500">Sauvegarder</button>
                        <button type="button" id="btn-publish" class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/15">Publier</button>
                        <button type="button" id="btn-preview" class="rounded-lg border border-zinc-700 px-4 py-2 text-xs font-semibold text-zinc-200 hover:bg-zinc-800">Rafraichir apercu</button>
                        <span class="text-xs text-zinc-500" id="template-status">Edition en cours...</span>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white">Preview temps reel</h2>
                @include('admin.mail-templates.preview')
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.getElementById('mail-template-editor');
            if (!root) return;
            const key = root.dataset.templateKey;
            const form = document.getElementById('template-form');
            const updateUrl = root.dataset.updateUrl;
            const previewUrl = root.dataset.previewUrl;
            const statusEl = document.getElementById('template-status');
            const versionEl = document.getElementById('template-version');
            const previewFrame = document.getElementById('mail-preview-frame');
            const previewSubject = document.getElementById('mail-preview-subject');
            const previewText = document.getElementById('mail-preview-text');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function setStatus(message, error = false) {
                statusEl.textContent = message;
                statusEl.classList.toggle('text-red-300', error);
                statusEl.classList.toggle('text-zinc-500', !error);
            }

            function parseJsonField(name) {
                const raw = form.elements[name].value;
                if (!raw || raw.trim() === '') return {};
                return JSON.parse(raw);
            }

            function buildPayload(publish = false) {
                return {
                    name: form.elements.name.value,
                    category: form.elements.category.value,
                    subject_template: form.elements.subject_template.value,
                    layout_json: parseJsonField('layout_json'),
                    content_json: parseJsonField('content_json'),
                    variables_json: parseJsonField('variables_json'),
                    is_active: form.elements.is_active.checked,
                    publish: publish
                };
            }

            async function save(publish = false) {
                try {
                    const payload = buildPayload(publish);
                    const res = await fetch(updateUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(payload)
                    });
                    if (!res.ok) throw new Error('Erreur de sauvegarde.');
                    const data = await res.json();
                    versionEl.textContent = data.version ?? versionEl.textContent;
                    setStatus(publish ? 'Template publie.' : 'Sauvegarde OK.');
                } catch (e) {
                    setStatus('JSON invalide ou sauvegarde impossible.', true);
                }
            }

            async function refreshPreview() {
                try {
                    const vars = parseJsonField('variables_json');
                    const res = await fetch(previewUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ vars })
                    });
                    if (!res.ok) throw new Error('Preview KO');
                    const data = await res.json();
                    previewSubject.textContent = data.subject || '';
                    previewText.textContent = data.text || '';
                    previewFrame.srcdoc = data.html || '';
                    setStatus('Apercu rafraichi.');
                } catch (e) {
                    setStatus('Impossible de generer l apercu.', true);
                }
            }

            document.getElementById('btn-save')?.addEventListener('click', () => save(false));
            document.getElementById('btn-publish')?.addEventListener('click', () => save(true));
            document.getElementById('btn-preview')?.addEventListener('click', refreshPreview);

            setInterval(() => save(false), 15000);
            setInterval(refreshPreview, 5000);
            refreshPreview();
        })();
    </script>
@endpush
