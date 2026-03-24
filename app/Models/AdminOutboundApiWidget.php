<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminOutboundApiWidget extends Model
{
    public const AUTH_NONE = 'none';

    public const AUTH_BEARER = 'bearer';

    public const AUTH_API_KEY_HEADER = 'api_key_header';

    public const AUTH_API_KEY_QUERY = 'api_key_query';

    public const AUTH_BASIC = 'basic';

    public const FETCH_LIVE = 'live';

    public const FETCH_SCHEDULED = 'scheduled';

    public const DISPLAY_RAW_JSON = 'raw_json';

    public const DISPLAY_KEY_PATHS = 'key_paths';

    protected $fillable = [
        'name',
        'title',
        'is_enabled',
        'sort_order',
        'http_method',
        'url',
        'query_params',
        'body',
        'headers',
        'auth_type',
        'auth_secret',
        'auth_header_name',
        'auth_query_param',
        'basic_username',
        'timeout_seconds',
        'fetch_mode',
        'cron_interval_minutes',
        'cached_fetched_at',
        'cached_status_code',
        'cached_body',
        'last_error',
        'display_mode',
        'display_paths',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'query_params' => 'array',
            'headers' => 'array',
            'display_paths' => 'array',
            'cached_fetched_at' => 'datetime',
            'auth_secret' => 'encrypted',
        ];
    }

    public function shouldRefreshOnSchedule(): bool
    {
        return $this->is_enabled
            && $this->fetch_mode === self::FETCH_SCHEDULED
            && $this->cron_interval_minutes !== null
            && $this->cron_interval_minutes > 0;
    }

    public function isDueForScheduledRefresh(): bool
    {
        if (! $this->shouldRefreshOnSchedule()) {
            return false;
        }
        if ($this->cached_fetched_at === null) {
            return true;
        }

        return $this->cached_fetched_at->copy()->addMinutes((int) $this->cron_interval_minutes)->isPast();
    }
}
