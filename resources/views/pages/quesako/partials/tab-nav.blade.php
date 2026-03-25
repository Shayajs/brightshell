<nav class="quesako-tabs" aria-label="Onglets quesako">
    @foreach($tabs as $tab)
        <a
            href="{{ route('quesako.tab', ['tabSlug' => $tab['slug']]) }}"
            @class(['quesako-tab', 'is-active' => $tab['slug'] === $activeTab['slug']])
            @if($tab['slug'] !== $activeTab['slug']) data-transition-link @endif
            aria-current="{{ $tab['slug'] === $activeTab['slug'] ? 'page' : 'false' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
