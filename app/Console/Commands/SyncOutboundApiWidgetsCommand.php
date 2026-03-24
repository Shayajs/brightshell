<?php

namespace App\Console\Commands;

use App\Models\AdminOutboundApiWidget;
use App\Services\OutboundApiRequestExecutor;
use Illuminate\Console\Command;

class SyncOutboundApiWidgetsCommand extends Command
{
    protected $signature = 'outbound-api-widgets:sync';

    protected $description = 'Rafraîchit les modules API externes configurés en mode planifié (cache).';

    public function handle(OutboundApiRequestExecutor $executor): int
    {
        $n = 0;
        AdminOutboundApiWidget::query()
            ->where('is_enabled', true)
            ->where('fetch_mode', AdminOutboundApiWidget::FETCH_SCHEDULED)
            ->orderBy('id')
            ->each(function (AdminOutboundApiWidget $widget) use ($executor, &$n): void {
                if (! $widget->isDueForScheduledRefresh()) {
                    return;
                }

                $r = $executor->execute($widget);
                $widget->update([
                    'cached_status_code' => $r['status_code'],
                    'cached_body' => $r['body'],
                    'cached_fetched_at' => now(),
                    'last_error' => $r['error'],
                ]);
                $n++;
            });

        if ($n > 0) {
            $this->info("Modules mis à jour : {$n}.");
        }

        return self::SUCCESS;
    }
}
