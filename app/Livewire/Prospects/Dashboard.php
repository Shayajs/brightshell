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
        // `score_breakdown.modifiers` est un objet associatif keyé par nom de modificateur ;
        // on cherche les prospects pour lesquels la clé `relais_generationnel` est présente.
        $relais = Prospect::query()
            ->withoutExcluded()
            ->whereRaw("JSON_EXTRACT(score_breakdown, '$.modifiers.relais_generationnel') IS NOT NULL")
            ->count();

        $tauxTraites = $total > 0 ? (int) round(($traites / $total) * 100) : 0;

        return view('prospects.partials.dashboard-cards', compact(
            'total', 'hot', 'priority', 'standard', 'traites', 'tauxTraites', 'relais'
        ));
    }
}
