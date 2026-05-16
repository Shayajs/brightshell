<?php

declare(strict_types=1);

namespace App\Jobs\Prospects;

use App\Actions\Prospects\ImportOptions;
use App\Actions\Prospects\ImportProspectsAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Job d'import déclenché depuis le composant Livewire ImportRunner.
 *
 * État de progression écrit dans le cache sous la clé `prospects:import:{importId}`,
 * lu via wire:poll dans l'UI.
 */
final class ImportProspectsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        public readonly ImportOptions $options,
    ) {}

    public function handle(ImportProspectsAction $action): void
    {
        $importId = $this->options->importId ?? 'default';
        $cacheKey = "prospects:import:{$importId}";

        Cache::put($cacheKey, [
            'status' => 'running',
            'current' => 0,
            'total' => 0,
            'step' => 'starting',
            'started_at' => now()->toIso8601String(),
        ], 3600);

        try {
            $result = $action->run($this->options, function (int $current, int $total, string $step) use ($cacheKey): void {
                Cache::put($cacheKey, [
                    'status' => 'running',
                    'current' => $current,
                    'total' => $total,
                    'step' => $step,
                ], 3600);
            });

            Cache::put($cacheKey, [
                'status' => 'done',
                'result' => $result->toArray(),
                'finished_at' => now()->toIso8601String(),
            ], 3600);
        } catch (\Throwable $e) {
            Log::error('[Prospects][Job] failed', ['error' => $e->getMessage(), 'options' => (array) $this->options]);
            Cache::put($cacheKey, [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 3600);
        }
    }
}
