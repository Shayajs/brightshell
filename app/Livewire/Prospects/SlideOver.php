<?php

declare(strict_types=1);

namespace App\Livewire\Prospects;

use App\Models\Prospect;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Panneau latéral détails (slide-over) + carte Leaflet + explicabilité du score.
 *
 * Activé via `dispatch('prospects:open', id: ...)` depuis Index.
 */
final class SlideOver extends Component
{
    public ?int $prospectId = null;
    public bool $open = false;

    #[On('prospects:open')]
    public function show(int $id): void
    {
        $this->prospectId = $id;
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->prospectId = null;
    }

    public function marquerTraite(): void
    {
        if ($this->prospectId === null) {
            return;
        }
        Prospect::query()->whereKey($this->prospectId)->update([
            'traite' => true,
            'traite_at' => now(),
        ]);
        $this->dispatch('prospects:updated');
    }

    public function render(): View
    {
        $prospect = $this->prospectId !== null
            ? Prospect::query()->find($this->prospectId)
            : null;

        return view('prospects.partials.slide-over', [
            'prospect' => $prospect,
            'isOpen' => $this->open && $prospect !== null,
            'homeLat' => config('prospects.home.lat'),
            'homeLong' => config('prospects.home.long'),
            'homeRadius' => config('prospects.home.radius_km'),
        ]);
    }
}
