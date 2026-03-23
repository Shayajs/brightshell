@php
    /** @var array{node: \App\Models\DocNode, children: array} $branch */
    $n = $branch['node'];
    $sub = $branch['children'];
    $path = $n->pathString();
    $cp = $currentPath ?? '';
    $isExact = $cp !== '' && $cp === $path;
    $isAncestor = $cp !== '' && $cp !== $path && str_starts_with($cp, $path.'/');
    $isActive = $isExact || $isAncestor;
@endphp
<li class="docs-nav-tree-node" role="none">
    <a
        href="{{ route('portals.docs.show', ['path' => $path]) }}"
        role="treeitem"
        @class([
            'flex min-w-0 items-center gap-2 rounded-lg px-2 py-1.5 text-left text-[13px] leading-snug transition',
            'bg-gradient-to-r from-indigo-500/20 to-violet-500/10 font-semibold text-white shadow-sm ring-1 ring-indigo-400/25' => $isExact,
            'text-indigo-200/95 hover:bg-zinc-800/60 hover:text-white' => $isAncestor && ! $isExact,
            'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-100' => ! $isActive,
        ])
    >
        @if ($n->is_folder)
            <svg class="h-4 w-4 shrink-0 text-amber-400/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M3 7v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z" stroke-linejoin="round"/>
            </svg>
        @else
            <svg class="h-4 w-4 shrink-0 text-violet-400/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linejoin="round"/>
                <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
            </svg>
        @endif
        <span class="min-w-0 flex-1 truncate">{{ $n->title }}</span>
    </a>
    @if ($sub !== [])
        <ul class="relative ml-2.5 mt-0.5 space-y-0.5 border-l border-zinc-700/50 py-0.5 pl-2.5" role="group">
            @foreach ($sub as $child)
                @include('portals.docs.partials.sidebar-tree-node', [
                    'branch' => $child,
                    'currentPath' => $currentPath,
                ])
            @endforeach
        </ul>
    @endif
</li>
