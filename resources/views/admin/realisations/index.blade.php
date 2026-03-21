@extends('layouts.admin')

@php $pageTitle = 'Réalisations'; @endphp

@section('content')
<div class="space-y-8">

    {{-- En-tête --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-100 font-display">Réalisations</h1>
            <p class="mt-1 text-sm text-zinc-400">Gérez vos projets affichés sur la page publique.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.realisations.create', ['category' => 'websites']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Site web
            </a>
            <a href="{{ route('admin.realisations.create', ['category' => 'personal']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-zinc-700 px-4 py-2 text-sm font-semibold text-zinc-100 hover:bg-zinc-600 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Perso
            </a>
        </div>
    </div>

    @include('layouts.partials.flash')

    {{-- Section Sites Web --}}
    <section>
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-zinc-500">Sites Web</h2>
        @if (count($data['websites']) > 0)
        <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900">
            <ul class="divide-y divide-zinc-800" id="sortable-websites">
                @foreach ($data['websites'] as $item)
                <li class="flex items-center gap-4 px-5 py-4 hover:bg-zinc-800/40 transition group" data-id="{{ $item['id'] }}">
                    {{-- Poignée drag --}}
                    <svg class="size-5 shrink-0 cursor-grab text-zinc-600 group-hover:text-zinc-400 sort-handle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>

                    {{-- Image --}}
                    <div class="size-12 shrink-0 overflow-hidden rounded-lg bg-zinc-800 border border-zinc-700">
                        @if (!empty($item['image']))
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" class="size-full object-cover">
                        @else
                            <div class="flex size-full items-center justify-center text-zinc-600">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4m-4-8h.01"/></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Infos --}}
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-zinc-100">{{ $item['title'] }}</p>
                        <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $item['url'] ?? '—' }}</p>
                        @if (!empty($item['tags']))
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach ($item['tags'] as $tag)
                                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-[10px] text-zinc-400">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Statut --}}
                    @if ($item['published'] ?? true)
                        <span class="shrink-0 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-400 ring-1 ring-emerald-500/20">Publié</span>
                    @else
                        <span class="shrink-0 rounded-full bg-zinc-700/50 px-2 py-0.5 text-[11px] font-medium text-zinc-500 ring-1 ring-zinc-600/30">Brouillon</span>
                    @endif

                    {{-- Actions --}}
                    <div class="flex shrink-0 items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                        <a href="{{ route('admin.realisations.edit', ['category' => 'websites', 'id' => $item['id']]) }}"
                           class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-100 transition">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.realisations.destroy', ['category' => 'websites', 'id' => $item['id']]) }}"
                              onsubmit="return confirm('Supprimer « {{ $item['title'] }} » ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-lg p-1.5 text-zinc-500 hover:bg-red-500/10 hover:text-red-400 transition">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @else
        <div class="rounded-xl border border-dashed border-zinc-700 p-8 text-center">
            <p class="text-zinc-500">Aucun site web renseigné.</p>
            <a href="{{ route('admin.realisations.create', ['category' => 'websites']) }}"
               class="mt-3 inline-flex items-center gap-2 rounded-lg bg-indigo-600/20 px-4 py-2 text-sm font-medium text-indigo-400 hover:bg-indigo-600/30 transition">
                Ajouter le premier projet
            </a>
        </div>
        @endif
    </section>

    {{-- Section Réalisations Perso --}}
    <section>
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-zinc-500">Réalisations Personnelles</h2>
        @if (count($data['personal']) > 0)
        <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900">
            <ul class="divide-y divide-zinc-800" id="sortable-personal">
                @foreach ($data['personal'] as $item)
                <li class="flex items-center gap-4 px-5 py-4 hover:bg-zinc-800/40 transition group" data-id="{{ $item['id'] }}">
                    <svg class="size-5 shrink-0 cursor-grab text-zinc-600 group-hover:text-zinc-400 sort-handle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>

                    <div class="size-12 shrink-0 overflow-hidden rounded-lg bg-zinc-800 border border-zinc-700">
                        @if (!empty($item['image']))
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" class="size-full object-cover">
                        @else
                            <div class="flex size-full items-center justify-center text-zinc-600">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4m-4-8h.01"/></svg>
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-zinc-100">{{ $item['title'] }}</p>
                        <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $item['demo_url'] ?? '—' }}</p>
                        @if (!empty($item['tags']))
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach ($item['tags'] as $tag)
                                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-[10px] text-zinc-400">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($item['published'] ?? true)
                        <span class="shrink-0 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-400 ring-1 ring-emerald-500/20">Publié</span>
                    @else
                        <span class="shrink-0 rounded-full bg-zinc-700/50 px-2 py-0.5 text-[11px] font-medium text-zinc-500 ring-1 ring-zinc-600/30">Brouillon</span>
                    @endif

                    <div class="flex shrink-0 items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                        <a href="{{ route('admin.realisations.edit', ['category' => 'personal', 'id' => $item['id']]) }}"
                           class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-100 transition">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.realisations.destroy', ['category' => 'personal', 'id' => $item['id']]) }}"
                              onsubmit="return confirm('Supprimer « {{ $item['title'] }} » ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-lg p-1.5 text-zinc-500 hover:bg-red-500/10 hover:text-red-400 transition">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @else
        <div class="rounded-xl border border-dashed border-zinc-700 p-8 text-center">
            <p class="text-zinc-500">Aucune réalisation personnelle.</p>
            <a href="{{ route('admin.realisations.create', ['category' => 'personal']) }}"
               class="mt-3 inline-flex items-center gap-2 rounded-lg bg-zinc-700/30 px-4 py-2 text-sm font-medium text-zinc-300 hover:bg-zinc-700/50 transition">
                Ajouter une réalisation
            </a>
        </div>
        @endif
    </section>

</div>
@endsection

@push('scripts')
<script>
// Drag & drop simple (sans lib externe) pour réordonner
function initSortable(listId, category) {
    const list = document.getElementById(listId);
    if (!list) return;

    let dragging = null;

    list.querySelectorAll('.sort-handle').forEach(handle => {
        handle.closest('li').setAttribute('draggable', 'true');
    });

    list.addEventListener('dragstart', e => {
        dragging = e.target.closest('li');
        dragging.classList.add('opacity-50');
    });
    list.addEventListener('dragend', e => {
        dragging.classList.remove('opacity-50');
        saveOrder(list, category);
        dragging = null;
    });
    list.addEventListener('dragover', e => {
        e.preventDefault();
        const over = e.target.closest('li');
        if (over && over !== dragging) {
            const rect = over.getBoundingClientRect();
            const next = (e.clientY - rect.top) > rect.height / 2;
            list.insertBefore(dragging, next ? over.nextSibling : over);
        }
    });
}

function saveOrder(list, category) {
    const ids = [...list.querySelectorAll('li[data-id]')].map(li => li.dataset.id);
    fetch('{{ route('admin.realisations.reorder') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
        },
        body: JSON.stringify({ category, ids }),
    });
}

initSortable('sortable-websites', 'websites');
initSortable('sortable-personal', 'personal');
</script>
@endpush
