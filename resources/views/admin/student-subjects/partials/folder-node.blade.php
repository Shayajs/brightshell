@php
    /** @var array{folder: \App\Models\StudentSubjectFolder, children: \Illuminate\Support\Collection} $node */
    $f = $node['folder'];
    $depth = $depth ?? 0;
    $ml = min($depth * 12, 96);
    $activeFolder = $activeFolder ?? null;
    $isActive = $activeFolder && (int) $activeFolder->id === (int) $f->id;
@endphp
<div class="rounded-xl border bg-zinc-950/40 transition-shadow {{ $isActive ? 'border-violet-500/40 ring-2 ring-violet-500/25' : 'border-zinc-800/90' }}" style="margin-left: {{ $ml }}px">
    <div class="flex flex-col gap-2 border-b border-zinc-800/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.student-subjects.show', [$user, $subject, 'dossier' => $f->id]) }}"
           class="flex min-w-0 flex-1 items-center gap-2 rounded-lg text-left hover:text-violet-200">
            <svg class="size-5 shrink-0 text-amber-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <span class="truncate font-medium text-zinc-100">{{ $f->name }}</span>
            @if ($isActive)
                <span class="shrink-0 rounded bg-violet-500/20 px-1.5 py-0.5 text-[9px] font-bold uppercase text-violet-300">Actif</span>
            @endif
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.student-subject-folders.edit', [$user, $subject, $f->id]) }}"
               class="rounded-md border border-zinc-600 px-2 py-1 text-[11px] font-semibold text-zinc-400 hover:bg-zinc-800">Dossier</a>
            <form method="POST" action="{{ route('admin.student-subject-folders.destroy', [$user, $subject, $f->id]) }}" onsubmit="return confirm('Supprimer ce dossier et tout ce qu’il contient ?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-md border border-red-500/30 px-2 py-1 text-[11px] font-semibold text-red-400 hover:bg-red-500/10">Supprimer</button>
            </form>
        </div>
    </div>

    <div class="space-y-2 px-4 py-3">
        @if ($f->files->isEmpty())
            <p class="text-xs text-zinc-600">Aucun fichier (utilisez le panneau de droite pour importer ou créer du MD).</p>
        @else
            <ul class="space-y-1.5">
                @foreach ($f->files as $file)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-zinc-900/60 px-3 py-2 text-sm">
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            {!! \App\Support\StudentMaterials\FileTypeIcon::svg($file) !!}
                            <span class="min-w-0 truncate text-zinc-300">{{ $file->original_name }}</span>
                            @if ($file->is_hidden_from_student)
                                <span class="shrink-0 text-[9px] font-bold uppercase text-zinc-600">masqué</span>
                            @endif
                            @if ($file->is_locked)
                                <span class="shrink-0 text-[9px] font-bold uppercase text-amber-600/90">verrou</span>
                            @endif
                        </div>
                        <span class="shrink-0 text-[10px] text-zinc-600">{{ $file->humanSize() }}</span>
                        <div class="flex w-full flex-wrap gap-2 sm:w-auto">
                            @if ($file->isMarkdown())
                                <a href="{{ route('admin.student-subject-files.markdown.edit', [$user, $subject, $file]) }}"
                                   class="text-[11px] font-semibold text-violet-400 hover:text-violet-300">Éditer MD</a>
                                <a href="{{ route('admin.student-subject-files.preview', $file->id) }}" target="_blank" rel="noopener"
                                   class="text-[11px] font-semibold text-zinc-400 hover:text-zinc-200">Aperçu</a>
                            @endif
                            <a href="{{ route('admin.student-subject-files.download', $file->id) }}"
                               class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300">Télécharger</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

@foreach ($node['children'] as $child)
    @include('admin.student-subjects.partials.folder-node', ['node' => $child, 'user' => $user, 'subject' => $subject, 'depth' => $depth + 1, 'activeFolder' => $activeFolder])
@endforeach
