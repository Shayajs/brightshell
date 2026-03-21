@php
    /** @var array{folder: \App\Models\StudentSubjectFolder, children: \Illuminate\Support\Collection} $node */
    $f = $node['folder'];
    $depth = $depth ?? 0;
    $ml = min($depth * 12, 96);
@endphp
<div class="rounded-xl border border-zinc-800/90 bg-zinc-950/40" style="margin-left: {{ $ml }}px">
    <div class="flex items-center gap-2 border-b border-zinc-800/80 px-4 py-3">
        <svg class="size-5 shrink-0 text-amber-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
        <span class="truncate font-medium text-zinc-100">{{ $f->name }}</span>
    </div>

    <div class="space-y-2 px-4 py-3">
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
                        <div class="flex shrink-0 flex-wrap gap-2">
                            @if ($file->isMarkdown())
                                <a href="{{ route('portals.courses.matieres.read', $file->id) }}"
                                   class="text-[11px] font-semibold text-emerald-400 hover:text-emerald-300">Lire</a>
                            @endif
                            <a href="{{ route('portals.courses.matieres.download', $file->id) }}"
                               class="text-[11px] font-semibold text-violet-400 hover:text-violet-300">Télécharger</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

@foreach ($node['children'] as $child)
    @include('portals.courses.matieres.partials.folder-node', ['node' => $child, 'depth' => $depth + 1])
@endforeach
