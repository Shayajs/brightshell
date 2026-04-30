@use('App\Models\ContactMessage')

<form
    method="post"
    action="{{ route('contact.store') }}"
    class="auth-form contact-project-form"
    data-contact-form="{{ ContactMessage::TYPE_PROJECT }}"
    enctype="multipart/form-data"
    novalidate
>
    @csrf
    <input type="hidden" name="type" value="{{ ContactMessage::TYPE_PROJECT }}">
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

    <div class="contact-project-grid">
        {{-- Colonne 1 : identité + cadrage --}}
        <div class="contact-project-col">
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="prj_first_name" class="auth-label">Prénom</label>
                    <input id="prj_first_name" type="text" name="first_name" value="{{ old('first_name', $prefillUser?->first_name) }}" required autocomplete="given-name" class="auth-input">
                    @if($activeType === ContactMessage::TYPE_PROJECT)
                        @error('first_name') <p class="auth-error">{{ $message }}</p> @enderror
                    @endif
                </div>
                <div>
                    <label for="prj_last_name" class="auth-label">Nom</label>
                    <input id="prj_last_name" type="text" name="last_name" value="{{ old('last_name', $prefillUser?->last_name) }}" required autocomplete="family-name" class="auth-input">
                    @if($activeType === ContactMessage::TYPE_PROJECT)
                        @error('last_name') <p class="auth-error">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="prj_email" class="auth-label">E-mail</label>
                    <input id="prj_email" type="email" name="email" value="{{ old('email', $prefillUser?->email) }}" required autocomplete="email" class="auth-input">
                    @if($activeType === ContactMessage::TYPE_PROJECT)
                        @error('email') <p class="auth-error">{{ $message }}</p> @enderror
                    @endif
                </div>
                <div>
                    <label for="prj_phone" class="auth-label">Téléphone <span class="contact-optional">(optionnel)</span></label>
                    <input id="prj_phone" type="tel" name="phone" value="{{ old('phone', $prefillUser?->phone) }}" autocomplete="tel" class="auth-input">
                </div>
            </div>

            <div>
                <label for="prj_company" class="auth-label">Société <span class="contact-optional">(optionnel)</span></label>
                <input id="prj_company" type="text" name="company" value="{{ old('company') }}" class="auth-input">
            </div>

            <div>
                <label for="prj_title" class="auth-label">Titre du projet</label>
                <input id="prj_title" type="text" name="project_title" value="{{ old('project_title') }}" required class="auth-input" placeholder="Ex. Plateforme SaaS de gestion d’équipes…">
                @if($activeType === ContactMessage::TYPE_PROJECT)
                    @error('project_title') <p class="auth-error">{{ $message }}</p> @enderror
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="prj_kind" class="auth-label">Type de projet</label>
                    <select id="prj_kind" name="project_kind" required class="auth-input">
                        <option value="" disabled @selected(! old('project_kind'))>Sélectionner…</option>
                        @foreach (ContactMessage::projectKindChoices() as $value => $label)
                            <option value="{{ $value }}" @selected(old('project_kind') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if($activeType === ContactMessage::TYPE_PROJECT)
                        @error('project_kind') <p class="auth-error">{{ $message }}</p> @enderror
                    @endif
                </div>
                <div>
                    <label for="prj_budget" class="auth-label">Budget estimé</label>
                    <select id="prj_budget" name="budget_range" required class="auth-input">
                        <option value="" disabled @selected(! old('budget_range'))>Sélectionner…</option>
                        @foreach (ContactMessage::budgetChoices() as $value => $label)
                            <option value="{{ $value }}" @selected(old('budget_range') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if($activeType === ContactMessage::TYPE_PROJECT)
                        @error('budget_range') <p class="auth-error">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>

            <div>
                <label for="prj_deadline" class="auth-label">Délai souhaité</label>
                <select id="prj_deadline" name="deadline" required class="auth-input">
                    <option value="" disabled @selected(! old('deadline'))>Sélectionner…</option>
                    @foreach (ContactMessage::deadlineChoices() as $value => $label)
                        <option value="{{ $value }}" @selected(old('deadline') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if($activeType === ContactMessage::TYPE_PROJECT)
                    @error('deadline') <p class="auth-error">{{ $message }}</p> @enderror
                @endif
            </div>

            <div>
                <label class="auth-label">Pièces jointes <span class="contact-optional">(jusqu’à 10 fichiers, 25 Mo / fichier)</span></label>
                <div class="contact-dropzone" data-contact-dropzone>
                    <input
                        type="file"
                        name="attachments[]"
                        id="prj_attachments"
                        multiple
                        accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.zip,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.md,.csv,.svg"
                        data-contact-files
                    >
                    <label for="prj_attachments" class="contact-dropzone__label">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <span>
                            <strong>Glissez-déposez vos fichiers</strong>
                            <br>
                            <span class="contact-dropzone__hint">ou cliquez pour parcourir · PDF, images, ZIP, docs office, txt, md…</span>
                        </span>
                    </label>
                    <ul class="contact-dropzone__list" data-contact-files-list></ul>
                </div>
                @if($activeType === ContactMessage::TYPE_PROJECT)
                    @error('attachments') <p class="auth-error">{{ $message }}</p> @enderror
                    @foreach ($errors->keys() as $key)
                        @if (str_starts_with($key, 'attachments.'))
                            <p class="auth-error">{{ $errors->first($key) }}</p>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Colonne 2 : éditeur Markdown + preview --}}
        <div class="contact-project-col">
            <div class="contact-md-editor">
                <div class="contact-md-editor__head">
                    <label for="prj_body" class="auth-label">Brief détaillé (Markdown supporté)</label>
                    <div class="contact-md-editor__tabs" role="tablist" aria-label="Édition Markdown">
                        <button type="button" class="contact-md-tab" data-contact-md-tab="write" aria-selected="true">Écrire</button>
                        <button type="button" class="contact-md-tab" data-contact-md-tab="preview" aria-selected="false">Aperçu</button>
                    </div>
                </div>

                <textarea
                    id="prj_body"
                    name="body"
                    rows="22"
                    required
                    maxlength="20000"
                    class="auth-input contact-textarea contact-md-editor__textarea"
                    data-contact-md-source
                    placeholder="# Mon projet&#10;&#10;## Contexte&#10;Décrivez votre activité, vos cibles…&#10;&#10;## Objectifs&#10;- Objectif 1&#10;- Objectif 2&#10;&#10;## Périmètre fonctionnel&#10;1. Authentification&#10;2. Tableau de bord&#10;3. ..."
                >{{ old('body') }}</textarea>

                <div class="contact-md-preview" data-contact-md-preview hidden>
                    <p class="contact-md-preview__placeholder">Commencez à écrire pour voir un aperçu.</p>
                </div>

                @if($activeType === ContactMessage::TYPE_PROJECT)
                    @error('body') <p class="auth-error">{{ $message }}</p> @enderror
                @endif

                <p class="contact-md-help">
                    Astuce : utilisez <code>#</code> pour les titres, <code>**gras**</code>, <code>- listes</code>, <code>```code```</code>.
                </p>
            </div>
        </div>
    </div>

    <button type="submit" class="auth-submit contact-project-submit">Soumettre mon projet</button>
</form>
