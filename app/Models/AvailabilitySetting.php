<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilitySetting extends Model
{
    protected $fillable = [
        'active',
        'weekdays',
        'start_time',
        'end_time',
        'slot_minutes',
        'horizon_weeks',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'weekdays' => 'array',
            'slot_minutes' => 'integer',
            'horizon_weeks' => 'integer',
        ];
    }

    /**
     * Récupère (ou crée en mémoire) la configuration unique de disponibilité.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new self([
            'active' => true,
            'weekdays' => [1, 2, 3, 4, 5],
            'start_time' => '09:00',
            'end_time' => '18:00',
            'slot_minutes' => 30,
            'horizon_weeks' => 8,
        ]);
    }
}
