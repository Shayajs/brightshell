@props([
    'label',
    'value',
    'accent' => 'text-white',
    'icon' => null,
    'hint' => null,
    'pulse' => false,
])

<div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-5 shadow-sm transition hover:border-slate-600 hover:bg-slate-800/60">
    <div class="flex items-start justify-between">
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">{{ $label }}</p>
        @if ($icon)
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-zinc-900/60 text-zinc-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $icon !!}</svg>
            </span>
        @endif
    </div>
    <p class="mt-3 text-3xl font-bold {{ $accent }} {{ $pulse ? 'animate-pulse' : '' }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-zinc-500">{{ $hint }}</p>
    @endif
</div>
