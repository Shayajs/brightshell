<?php

declare(strict_types=1);

namespace App\Livewire\Prospects;

use App\Actions\Prospects\ImportOptions;
use App\Jobs\Prospects\ImportProspectsJob;
use App\Services\Prospects\InpiPisteClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Lancement d'un import depuis l'UI + suivi de progression via wire:poll.1s.
 *
 * État du job stocké dans le cache (`prospects:import:{importId}`).
 */
final class ImportRunner extends Component
{
    public string $codePostal = '';
    public string $departement = '';
    public string $codeNaf = '';
    public int $maxPages = 4;
    public bool $withBodacc = true;
    public bool $withGeocoding = true;
    public bool $withInpi = false;

    public ?string $importId = null;

    /**
     * Indique si une exécution est en cours pour activer le polling.
     */
    public function getIsRunningProperty(): bool
    {
        if ($this->importId === null) {
            return false;
        }
        $state = Cache::get("prospects:import:{$this->importId}");

        return is_array($state) && ($state['status'] ?? null) === 'running';
    }

    public function start(): void
    {
        $this->validate([
            'codePostal' => ['nullable', 'string', 'max:10'],
            'departement' => ['nullable', 'string', 'max:3'],
            'codeNaf' => ['nullable', 'string', 'max:8'],
            'maxPages' => ['integer', 'min:1', 'max:40'],
        ]);

        $this->importId = (string) Str::uuid();

        $options = new ImportOptions(
            codePostal: $this->codePostal !== '' ? $this->codePostal : null,
            departement: $this->departement !== '' ? $this->departement : null,
            codeNaf: $this->codeNaf !== '' ? $this->codeNaf : null,
            maxPages: $this->maxPages,
            withBodacc: $this->withBodacc,
            withGeocoding: $this->withGeocoding,
            withInpi: $this->withInpi,
            importId: $this->importId,
        );

        ImportProspectsJob::dispatch($options);
    }

    public function render(View $view = null): View
    {
        $state = $this->importId !== null
            ? Cache::get("prospects:import:{$this->importId}")
            : null;

        $inpi = app(InpiPisteClient::class);

        return view('prospects.partials.import-runner', [
            'state' => is_array($state) ? $state : null,
            'inpiEnabled' => $inpi->isEnabled(),
        ]);
    }
}
