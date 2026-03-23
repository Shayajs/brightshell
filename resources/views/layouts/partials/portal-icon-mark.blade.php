@php
    $key = $key ?? 'settings';
    $frame = $frame ?? 'md';
    $extraClass = trim($class ?? '');
    $pad = match ($frame) {
        'xs' => 'rounded-md p-1',
        'sm' => 'rounded-lg p-1.5',
        'md' => 'rounded-xl p-2',
        'lg' => 'rounded-2xl p-3',
        'xl' => 'rounded-2xl p-3.5 sm:p-4',
        default => 'rounded-xl p-2',
    };
    $svg = match ($frame) {
        'xs' => 'h-3.5 w-3.5',
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-7 w-7',
        'xl' => 'h-9 w-9 sm:h-10 sm:w-10',
        default => 'h-5 w-5',
    };
    $badge = \App\Support\PortalNavigation::iconBadgeClasses($key);
@endphp
<span @class(['inline-flex shrink-0 items-center justify-center ring-1 ring-inset shadow-sm', $pad, $badge, $extraClass !== '' ? $extraClass : null])>
    <svg class="{{ $svg }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        {!! \App\Support\PortalNavigation::iconSvg($key) !!}
    </svg>
</span>
