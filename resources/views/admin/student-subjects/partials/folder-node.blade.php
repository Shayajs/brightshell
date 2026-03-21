@php
    /** @var array{folder: \App\Models\StudentSubjectFolder, children: \Illuminate\Support\Collection} $node */
    $f = $node['folder'];
    $depth = $depth ?? 0;
    $ml = min($depth * 12, 96);
@endphp
<div class="rounded-xl border border-zinc-800/90 bg-zinc-950/40" style="margin-left: {{ $ml }}px">
    <div class="flex flex-col gap-3 border-b border-zinc-800/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-2">
            <svg class="size-5 shrink-0 text-amber-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <span class="truncate font-medium text-zinc-100">{{ $f->name }}</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.student-subject-folders.edit', [$user, $subject, $f->id]) }}"
               class="rounded-md border border-zinc-600 px-2 py-1 text-[11px] font-semibold text-zinc-400 hover:bg-zinc-800">Dossier</a>
            <form method="POST" action="{{ route('admin.student-subject-folders.destroy', [$user, $subject, $f->id]) }}" onsubmit="return confirm('Supprimer ce dossier et tout ce qu’il contient ?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-md border border-red-500/30 px-2 py-1 text-[11px] font-semibold text-red-400 hover:bg-red-500/10">Supprimer</button>
            </form>
        </div>
    </div>

    <div class="space-y-3 px-4 py-3">
        {{-- Fichiers --}}
        @if ($f->files->isEmpty())
            <p class="text-xs text-zinc-600">Aucun fichier dans ce dossier.</p>
        @else
            <ul class="space-y-1.5">
                @foreach ($f->files as $file)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-zinc-900/60 px-3 py-2 text-sm">
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            {!! \App\Support\StudentMaterials\FileTypeIcon::svg($file) !!}
                            <span class="min-w-0 truncate text-zinc-300">{{ $file->original_name }}</span>
                        </div>
                        <span class="shrink-0 text-[10px] text-zinc-600">{{ $file->humanSize() }}</span>
                        <div class="flex flex-wrap gap-2">
                            @if ($file->isMarkdown())
                                <a href="{{ route('admin.student-subject-files.preview', $file->id) }}" target="_blank" rel="noopener"
                                   class="text-[11px] font-semibold text-violet-400 hover:text-violet-300">Aperçu</a>
                            @endif
                            <a href="{{ route('admin.student-subject-files.download', $file->id) }}"
                               class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300">Télécharger</a>
                            <form method="POST" action="{{ route('admin.student-subject-files.destroy', [$user, $subject, $file->id]) }}" class="inline" onsubmit="return confirm('Supprimer ce fichier ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[11px] font-semibold text-red-400 hover:text-red-300">Supprimer</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.student-subject-files.store', [$user, $subject]) }}" enctype="multipart/form-data" class="flex flex-col gap-2 rounded-lg border border-dashed border-zinc-700 p-3">
            @csrf
            <input type="hidden" name="student_subject_folder_id" value="{{ $f->id }}">
            <label class="text-[11px] font-medium text-zinc-500">Ajouter des fichiers</label>
            <input type="file" name="files[]" multiple required
                   class="text-xs text-zinc-400 file:mr-2 file:rounded-md file:border-0 file:bg-violet-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-violet-500">
            <p class="text-[10px] text-zinc-600">Inclut .md (Obsidian), PDF, images, etc.</p>
            <button type="submit" class="self-start rounded-md bg-violet-600/90 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-violet-500">Envoyer</button>
        </form>

        <form method="POST" action="{{ route('admin.student-subject-files.store-markdown', [$user, $subject]) }}" class="flex flex-col gap-2 rounded-lg border border-dashed border-violet-500/35 bg-violet-500/5 p-3">
            @csrf
            <input type="hidden" name="student_subject_folder_id" value="{{ $f->id }}">
            <label class="text-[11px] font-semibold text-violet-300">Créer / coller une note Markdown</label>
            <p class="text-[10px] text-zinc-500">Callouts Obsidian <code class="rounded bg-zinc-950 px-1">&gt; [!note]</code>, GFM, LaTeX <code class="rounded bg-zinc-950 px-1">$...$</code> / <code class="rounded bg-zinc-950 px-1">$$...$$</code></p>
            <input type="text" name="markdown_title" value="{{ old('markdown_title') }}" required placeholder="Nom du fichier (ex. Cours-3)"
                   class="rounded-lg border border-zinc-700 bg-zinc-800 px-2.5 py-1.5 text-sm text-zinc-100 placeholder-zinc-600">
            <textarea name="markdown_body" rows="8" required placeholder="# Titre&#10;&#10;&gt; [!tip] Astuce&#10;&gt; Contenu du callout...&#10;&#10;Formule : $E = mc^2$"
                      class="rounded-lg border border-zinc-700 bg-zinc-950 px-2.5 py-2 font-mono text-xs text-zinc-200 placeholder-zinc-600">{{ old('markdown_body') }}</textarea>
            <button type="submit" class="self-start rounded-md bg-violet-700 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-violet-600">Enregistrer en .md</button>
        </form>

        {{-- Sous-dossier --}}
        <form method="POST" action="{{ route('admin.student-subject-folders.store', [$user, $subject]) }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $f->id }}">
            <div class="min-w-0 flex-1">
                <label class="text-[11px] text-zinc-500">Sous-dossier</label>
                <input type="text" name="name" required placeholder="Nom du sous-dossier"
                       class="mt-0.5 w-full rounded-lg border border-zinc-700 bg-zinc-800 px-2.5 py-1.5 text-sm text-zinc-100 placeholder-zinc-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500/30">
            </div>
            <button type="submit" class="shrink-0 rounded-lg bg-zinc-700 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-600">Créer</button>
        </form>
    </div>
</div>

@foreach ($node['children'] as $child)
    @include('admin.student-subjects.partials.folder-node', ['node' => $child, 'user' => $user, 'subject' => $subject, 'depth' => $depth + 1])
@endforeach
