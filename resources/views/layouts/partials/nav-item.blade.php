<a
    href="{{ $href }}"
    @class([
        'flex min-w-0 w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
        'bg-indigo-500/10 text-white ring-1 ring-inset ring-indigo-500/20' => $active,
        'text-zinc-400 hover:bg-zinc-800/60 hover:text-zinc-100' => !$active,
    ])
    @if($active) aria-current="page" @endif
>
    <svg class="h-4 w-4 shrink-0 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">{!! $icon !!}</svg>
    <span class="truncate">{{ $label }}</span>
</a>
