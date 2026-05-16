@props(['prospect'])

@php
    $domainSafe = $prospect->domaine_web ? rawurlencode($prospect->domaine_web) : null;
    $initials = $prospect->initiales;
    $fallbackId = 'plogo-' . $prospect->id;
@endphp

<div class="relative h-9 w-9 shrink-0">
    @if ($prospect->domaine_web)
        <img
            src="https://logo.clearbit.com/{{ $domainSafe }}?size=80"
            alt="{{ $prospect->nom_entreprise }}"
            loading="lazy"
            class="h-9 w-9 rounded-md border border-zinc-800 bg-zinc-900 object-contain p-0.5"
            onerror="this.style.display='none'; document.getElementById('{{ $fallbackId }}').style.display='flex'"
        />
        <span id="{{ $fallbackId }}"
              style="display:none"
              class="absolute inset-0 inline-flex items-center justify-center rounded-md border border-zinc-800 bg-zinc-900 text-xs font-bold text-zinc-300">
            {{ $initials }}
        </span>
    @else
        <span class="absolute inset-0 inline-flex items-center justify-center rounded-md border border-zinc-800 bg-zinc-900 text-xs font-bold text-zinc-300">
            {{ $initials }}
        </span>
    @endif
</div>
