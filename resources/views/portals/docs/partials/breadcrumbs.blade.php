@php
    /** @var \App\Models\DocNode|null $node */
@endphp
<nav class="docs-breadcrumb mb-8 flex flex-wrap items-center gap-1.5 text-[13px]" aria-label="Fil d’Ariane">
    <a
        href="{{ route('portals.docs') }}"
        @class([
            'rounded-lg px-2.5 py-1 font-medium transition',
            'bg-white/10 text-white' => $node === null,
            'text-zinc-400 hover:bg-zinc-800/60 hover:text-indigo-300' => $node !== null,
        ])
    >
        Sommaire
    </a>
    @if ($node !== null)
        @php $segments = $node->pathSegments(); @endphp
        @foreach ($segments as $i => $seg)
            @php $partial = implode('/', array_slice($segments, 0, $i + 1)); @endphp
            <span class="select-none text-zinc-600" aria-hidden="true">/</span>
            @if ($i < count($segments) - 1)
                <a
                    href="{{ route('portals.docs.show', ['path' => $partial]) }}"
                    class="rounded-lg px-2.5 py-1 font-medium text-zinc-400 transition hover:bg-zinc-800/60 hover:text-indigo-300"
                >{{ str($seg)->replace('-', ' ')->title() }}</a>
            @else
                <span class="rounded-lg bg-gradient-to-r from-indigo-500/25 to-violet-500/15 px-2.5 py-1 font-semibold text-white ring-1 ring-indigo-400/20">{{ $node->title }}</span>
            @endif
        @endforeach
    @endif
</nav>
