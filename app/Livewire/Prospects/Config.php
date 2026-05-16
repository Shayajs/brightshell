<?php

declare(strict_types=1);

namespace App\Livewire\Prospects;

use App\Services\Prospects\InpiPisteClient;
use App\Services\Prospects\InseeSireneClient;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Visualisation lecture seule de la config scoring (config/prospects.php).
 *
 * Permet de comprendre pourquoi un prospect a tel score sans plonger dans le code.
 */
final class Config extends Component
{
    public function render(): View
    {
        return view('prospects.partials.config', [
            'home' => (array) config('prospects.home', []),
            'scoring' => (array) config('prospects.scoring', []),
            'modifiers' => (array) config('prospects.modifiers', []),
            'enrichment' => (array) config('prospects.enrichment', []),
            'inseeEnabled' => app(InseeSireneClient::class)->isEnabled(),
            'inpiEnabled' => app(InpiPisteClient::class)->isEnabled(),
        ]);
    }
}
