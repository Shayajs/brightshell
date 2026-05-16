@props(['website' => 0, 'software' => 0, 'max' => 150])

@php
    $wPct = $max > 0 ? min(100, (int) round(($website / $max) * 100)) : 0;
    $sPct = $max > 0 ? min(100, (int) round(($software / $max) * 100)) : 0;
@endphp

<div class="flex flex-col gap-1" title="Web {{ $website }} · Soft {{ $software }}">
    <div class="flex items-center gap-1.5">
        <span class="w-4 text-[9px] font-bold uppercase tracking-wider text-cyan-400">W</span>
        <div class="h-1 w-16 overflow-hidden rounded bg-zinc-800">
            <div class="h-full rounded bg-cyan-400" style="width: {{ $wPct }}%"></div>
        </div>
        <span class="text-[10px] tabular-nums text-zinc-400">{{ $website }}</span>
    </div>
    <div class="flex items-center gap-1.5">
        <span class="w-4 text-[9px] font-bold uppercase tracking-wider text-purple-400">S</span>
        <div class="h-1 w-16 overflow-hidden rounded bg-zinc-800">
            <div class="h-full rounded bg-purple-400" style="width: {{ $sPct }}%"></div>
        </div>
        <span class="text-[10px] tabular-nums text-zinc-400">{{ $software }}</span>
    </div>
</div>
