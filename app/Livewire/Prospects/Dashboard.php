<?php

declare(strict_types=1);

namespace App\Livewire\Prospects;

use App\Models\Prospect;
use App\Services\Prospects\Scoring\ScoreBand;
use Illuminate\View\View;
use Livewire\Component;

/**
 * KPI cards du tableau de bord prospects.
 *
 * Lecture seule, recomptes via `wire:poll.30s` pour suivre les imports en cours.
 */
final class Dashboard extends Component
{
    public function render(): View
    {
        $total = Prospect::query()->withoutExcluded()->count();
        $hot = Prospect::query()->band(ScoreBand::Hot)->count();
        $priority = Prospect::query()->band(ScoreBand::Priority)->count();
        $standard = Prospect::query()->band(ScoreBand::Standard)->count();
        $traites = Prospect::query()->withoutExcluded()->where('traite', true)->count();
        $relais = Prospect::query()
            ->whereJsonContains('score_breakdown->modifiers->relais_generationnel->multiplier', 1.5)
            ->count();

        $tauxTraites = $total > 0 ? (int) round(($traites / $total) * 100) : 0;

        return view('prospects.partials.dashboard-cards', compact(
            'total', 'hot', 'priority', 'standard', 'traites', 'tauxTraites', 'relais'
        ));
    }
}
