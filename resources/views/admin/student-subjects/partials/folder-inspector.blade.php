@php
    /** @var \App\Models\User $user */
    /** @var \App\Models\StudentSubject $subject */
    /** @var \App\Models\StudentSubjectFolder|null $activeFolder */
@endphp
<div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 xl:sticky xl:top-24">
    <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">Dossier sélectionné</h2>

    @if (! $activeFolder)
        <p class="mt-4 text-sm text-zinc-500">Créez d’abord un dossier racine, puis cliquez sur un dossier dans la colonne de gauche pour afficher import, Markdown et réglages d’accès élève.</p>
    @else
        <div class="mt-3 flex flex-wrap items-start justify-between gap-3 border-b border-zinc-800/80 pb-4">
            <div class="flex min-w-0 items-center gap-2">
                <svg class="size-5 shrink-0 text-amber-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <div class="min-w-0">
                    <p class="truncate font-medium text-zinc-100">{{ $activeFolder->name }}</p>
                    <p class="text-[10px] text-zinc-600">ID {{ $activeFolder->id }}@if ($activeFolder->parent_id) · sous-dossier @else · racine @endif</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.student-subject-folders.edit', [$user, $subject, $activeFolder->id]) }}"
                   class="rounded-md border border-zinc-600 px-2 py-1 text-[11px] font-semibold text-zinc-400 hover:bg-zinc-800">Renommer / déplacer</a>
                <form method="POST" action="{{ route('admin.student-subject-folders.destroy', [$user, $subject, $activeFolder->id]) }}"
                      onsubmit="return confirm('Supprimer ce dossier et tout son contenu ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded-md border border-red-500/30 px-2 py-1 text-[11px] font-semibold text-red-400 hover:bg-red-500/10">Supprimer</button>
                </form>
            </div>
        </div>

        <div class="mt-4 space-y-5">
            <div>
                <a href="{{ route('admin.student-subject-files.markdown.create', [$user, $subject, $activeFolder]) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-500">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Créer MD
                </a>
                <p class="mt-2 text-[10px] text-zinc-500">Éditeur avec aperçu (callouts Obsidian, GFM, LaTeX).</p>
            </div>

            <form method="POST" action="{{ route('admin.student-subject-files.store', [$user, $subject]) }}" enctype="multipart/form-data" class="flex flex-col gap-2 rounded-xl border border-dashed border-zinc-700 p-4">
                @csrf
                <input type="hidden" name="student_subject_folder_id" value="{{ $activeFolder->id }}">
                <label class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Importer des fichiers</label>
                <input type="file" name="files[]" multiple required
                       class="text-xs text-zinc-400 file:mr-2 file:rounded-md file:border-0 file:bg-zinc-700 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-600">
                <button type="submit" class="self-start rounded-md bg-zinc-700 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-zinc-600">Envoyer dans ce dossier</button>
            </form>

            <form method="POST" action="{{ route('admin.student-subject-folders.store', [$user, $subject]) }}" class="flex flex-col gap-2 rounded-xl border border-zinc-800/90 p-4">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $activeFolder->id }}">
                <label class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nouveau sous-dossier ici</label>
                <input type="text" name="name" required placeholder="Nom du sous-dossier"
                       class="rounded-lg border border-zinc-700 bg-zinc-800 px-2.5 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500/30">
                <button type="submit" class="self-start rounded-lg bg-zinc-700 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-600">Créer le sous-dossier</button>
            </form>

            <div class="rounded-xl border border-zinc-800/90 p-4">
                <h3 class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fichiers &amp; accès élève</h3>
                <p class="mt-1 text-[10px] text-zinc-600">Masqué : l’élève ne voit pas le fichier. Verrouillé : visible mais lecture / téléchargement bloqués jusqu’au déverrouillage.</p>

                @if ($activeFolder->files->isEmpty())
                    <p class="mt-3 text-xs text-zinc-600">Aucun fichier dans ce dossier.</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($activeFolder->files as $file)
                            <li class="rounded-lg border border-zinc-800 bg-zinc-950/50 p-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    {!! \App\Support\StudentMaterials\FileTypeIcon::svg($file) !!}
                                    <span class="min-w-0 flex-1 truncate text-sm text-zinc-200">{{ $file->original_name }}</span>
                                    @if ($file->is_hidden_from_student)
                                        <span class="rounded bg-zinc-800 px-1.5 py-0.5 text-[9px] font-bold uppercase text-zinc-500">Masqué</span>
                                    @endif
                                    @if ($file->is_locked)
                                        <span class="rounded bg-amber-500/15 px-1.5 py-0.5 text-[9px] font-bold uppercase text-amber-400">Verrouillé</span>
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2 text-[11px]">
                                    @if ($file->isMarkdown())
                                        <a href="{{ route('admin.student-subject-files.markdown.edit', [$user, $subject, $file]) }}"
                                           class="font-semibold text-violet-400 hover:text-violet-300">Éditer MD</a>
                                        <a href="{{ route('admin.student-subject-files.preview', $file->id) }}" target="_blank" rel="noopener"
                                           class="font-semibold text-zinc-400 hover:text-zinc-200">Aperçu</a>
                                    @endif
                                    <a href="{{ route('admin.student-subject-files.download', $file->id) }}"
                                       class="font-semibold text-indigo-400 hover:text-indigo-300">Télécharger</a>
                                </div>
                                <form method="POST" action="{{ route('admin.student-subject-files.update-access', [$user, $subject, $file]) }}" class="mt-3 space-y-2 border-t border-zinc-800/80 pt-3">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_locked" value="0">
                                    <input type="hidden" name="is_hidden_from_student" value="0">
                                    <label class="flex cursor-pointer items-center gap-2 text-xs text-zinc-400">
                                        <input type="checkbox" name="is_locked" value="1" @checked($file->is_locked)
                                               class="rounded border-zinc-600 bg-zinc-800 text-amber-500 focus:ring-amber-500/30">
                                        Verrouillé pour l’élève
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 text-xs text-zinc-400">
                                        <input type="checkbox" name="is_hidden_from_student" value="1" @checked($file->is_hidden_from_student)
                                               class="rounded border-zinc-600 bg-zinc-800 text-zinc-500 focus:ring-violet-500/30">
                                        Invisible pour l’élève
                                    </label>
                                    <button type="submit" class="rounded-md border border-zinc-600 px-2 py-1 text-[10px] font-semibold text-zinc-300 hover:bg-zinc-800">Appliquer</button>
                                </form>
                                <form method="POST" action="{{ route('admin.student-subject-files.destroy', [$user, $subject, $file->id]) }}" class="mt-2" onsubmit="return confirm('Supprimer ce fichier ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[10px] font-semibold text-red-400 hover:text-red-300">Supprimer le fichier</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
</div>
