<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" wire:poll.30s>
    <x-prospects.kpi-card
        label="Total prospects"
        :value="$total"
        accent="text-white"
        icon='<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>'
    />
    <x-prospects.kpi-card
        label="Hot (≥120)"
        :value="$hot"
        :hint="$relais ? $relais . ' avec relais générationnel' : null"
        accent="text-red-400"
        :pulse="$hot > 0"
        icon='<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>'
    />
    <x-prospects.kpi-card
        label="Prioritaires (80-119)"
        :value="$priority"
        accent="text-orange-400"
        icon='<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'
    />
    <x-prospects.kpi-card
        label="Taux traités"
        :value="$tauxTraites . '%'"
        :hint="$traites . ' / ' . $total . ' marqués traités'"
        accent="text-emerald-400"
        icon='<polyline points="20 6 9 17 4 12"/>'
    />
</div>
