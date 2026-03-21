@php
    /** @var string $current */
    $tabs = [
        ['key' => 'dashboard', 'label' => 'Tableau de bord', 'route' => 'portals.settings', 'params' => []],
        ['key' => 'profile', 'label' => 'Profil', 'route' => 'portals.settings.profile.edit', 'params' => []],
        ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'portals.settings.notifications.edit', 'params' => []],
        ['key' => 'security', 'label' => 'Sécurité', 'route' => 'portals.settings.security.edit', 'params' => []],
    ];
@endphp
<nav class="mb-8 flex flex-wrap gap-2 border-b border-zinc-800 pb-4" aria-label="Sections réglages">
    @foreach ($tabs as $tab)
        @php
            $active = $tab['key'] === $current;
            $href = route($tab['route'], $tab['params']);
        @endphp
        <a href="{{ $href }}"
           @class([
               'rounded-lg px-3 py-2 text-xs font-semibold transition sm:text-sm',
               'bg-indigo-500/15 text-indigo-200 ring-1 ring-indigo-500/30' => $active,
               'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100' => ! $active,
           ])
           @if ($active) aria-current="page" @endif>
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
