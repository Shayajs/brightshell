@php
    /** @var \App\Models\Project $project */
    $items = [
        ['route' => 'portals.project.show', 'match' => 'portals.project.show', 'label' => 'Vue d’ensemble'],
        ['route' => 'portals.project.appointments.index', 'match' => 'portals.project.appointments.*', 'label' => 'Rendez-vous'],
        ['route' => 'portals.project.notes.index', 'match' => 'portals.project.notes.*', 'label' => 'Notes'],
        ['route' => 'portals.project.kanban.index', 'match' => 'portals.project.kanban.*', 'label' => 'Kanban'],
        ['route' => 'portals.project.requests.index', 'match' => 'portals.project.requests.*', 'label' => 'Demandes'],
        ['route' => 'portals.project.prices.index', 'match' => 'portals.project.prices.*', 'label' => 'Prix & devis'],
        ['route' => 'portals.project.documents.index', 'match' => 'portals.project.documents.*', 'label' => 'Documents'],
        ['route' => 'portals.project.specs.index', 'match' => 'portals.project.specs.*', 'label' => 'Cahier des charges'],
        ['route' => 'portals.project.contracts.index', 'match' => 'portals.project.contracts.*', 'label' => 'Contrats'],
    ];
@endphp
<nav class="flex flex-wrap gap-2 border-b border-zinc-800 pb-4" aria-label="Sections du projet">
    @foreach ($items as $item)
        <a
            href="{{ route($item['route'], $project) }}"
            @class([
                'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                'bg-cyan-500/15 text-cyan-200 ring-1 ring-cyan-500/25' => request()->routeIs($item['match']),
                'text-zinc-400 hover:bg-zinc-800/60 hover:text-zinc-100' => ! request()->routeIs($item['match']),
            ])
        >{{ $item['label'] }}</a>
    @endforeach
</nav>
