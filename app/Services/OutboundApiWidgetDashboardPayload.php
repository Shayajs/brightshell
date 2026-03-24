<?php

namespace App\Services;

use App\Models\AdminOutboundApiWidget;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;

/**
 * Prépare les cartes « API perso » pour le tableau de bord admin.
 */
final class OutboundApiWidgetDashboardPayload
{
    public function __construct(
        private readonly OutboundApiRequestExecutor $executor,
    ) {}

    /**
     * @return list<array{
     *   id: int,
     *   title: string,
     *   name: string,
     *   fetch_mode: string,
     *   fetch_label: string,
     *   status_code: int|null,
     *   fetched_at: string|null,
     *   error: string|null,
     *   body_display: string,
     *   display_mode: string,
     * }>
     */
    public function forDashboard(): array
    {
        $widgets = AdminOutboundApiWidget::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $out = [];
        foreach ($widgets as $w) {
            $out[] = $this->one($w);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function one(AdminOutboundApiWidget $widget): array
    {
        $fetchMode = $widget->fetch_mode;
        $result = null;

        if ($fetchMode === AdminOutboundApiWidget::FETCH_LIVE) {
            $result = $this->executor->execute($widget);
        } else {
            $result = [
                'ok' => $widget->last_error === null,
                'status_code' => $widget->cached_status_code,
                'body' => $widget->cached_body,
                'error' => $widget->last_error,
            ];
        }

        $body = $result['body'] ?? '';
        $bodyDisplay = $this->formatBody($widget, (string) $body);

        return [
            'id' => $widget->id,
            'title' => $widget->title,
            'name' => $widget->name,
            'fetch_mode' => $fetchMode,
            'fetch_label' => $fetchMode === AdminOutboundApiWidget::FETCH_LIVE
                ? 'À la demande'
                : ('Planifié (toutes les '.$widget->cron_interval_minutes.' min)'),
            'status_code' => $result['status_code'] ?? $widget->cached_status_code,
            'fetched_at' => $fetchMode === AdminOutboundApiWidget::FETCH_LIVE
                ? now()->timezone(config('app.timezone'))->translatedFormat('j M Y, H:i:s')
                : ($widget->cached_fetched_at?->timezone(config('app.timezone'))->translatedFormat('j M Y, H:i:s')),
            'error' => $result['error'] ?? null,
            'body_display' => $bodyDisplay,
            'display_mode' => $widget->display_mode,
        ];
    }

    private function formatBody(AdminOutboundApiWidget $widget, string $body): string
    {
        if ($body === '') {
            return $widget->last_error === null ? '(vide)' : '';
        }

        if ($widget->display_mode === AdminOutboundApiWidget::DISPLAY_KEY_PATHS && is_array($widget->display_paths)) {
            $decoded = json_decode($body, true);
            if (! is_array($decoded)) {
                return Str::limit($body, 8000);
            }

            $lines = [];
            foreach ($widget->display_paths as $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }
                $val = Arr::get($decoded, $path);
                try {
                    $lines[] = $path.' = '.json_encode($val, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    $lines[] = $path.' = '.var_export($val, true);
                }
            }

            return $lines !== [] ? implode("\n", $lines) : Str::limit($body, 8000);
        }

        $pretty = json_decode($body, true);
        if (is_array($pretty)) {
            try {
                return json_encode($pretty, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return Str::limit($body, 8000);
            }
        }

        return Str::limit($body, 8000);
    }
}
