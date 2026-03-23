@php
    $branches = $docNavTree ?? [];
    $currentPath = $docNavCurrentPath ?? null;
@endphp
@if ($branches !== [])
    <div class="docs-nav-tree mt-3 border-t border-zinc-800/80 pt-3">
        <p class="mb-2 px-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-500">Arborescence</p>
        <ul class="docs-nav-tree-root space-y-0.5" role="tree">
            @foreach ($branches as $branch)
                @include('portals.docs.partials.sidebar-tree-node', [
                    'branch' => $branch,
                    'currentPath' => $currentPath,
                ])
            @endforeach
        </ul>
    </div>
@endif
