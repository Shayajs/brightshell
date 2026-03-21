@php
    $links = [
        ['admin.declarations.index', 'Vue d’ensemble'],
        ['admin.declarations.business.edit', 'Mon entreprise'],
        ['admin.declarations.urssaf', 'URSSAF &amp; charges'],
    ];
@endphp
<nav class="flex flex-wrap gap-2 border-b border-zinc-800 pb-4" aria-label="Déclarations">
    @foreach ($links as [$route, $label])
        <a href="{{ route($route) }}"
           @class([
               'rounded-lg px-3 py-2 text-sm font-semibold transition',
               'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/30' => request()->routeIs($route),
               'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' => ! request()->routeIs($route),
           ])>{!! $label !!}</a>
    @endforeach
</nav>
