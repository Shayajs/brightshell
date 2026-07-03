<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AppointmentSlot extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_BOOKED = 'booked';

    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'starts_at',
        'ends_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** @return HasOne<AppointmentBooking, $this> */
    public function booking(): HasOne
    {
        return $this->hasOne(AppointmentBooking::class);
    }

    /** @param Builder<static> $query */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /** @param Builder<static> $query */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    /** @return array<string, string> */
    public static function statusChoices(): array
    {
        return [
            self::STATUS_OPEN => 'Disponible',
            self::STATUS_BOOKED => 'Réservé',
            self::STATUS_BLOCKED => 'Bloqué',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusChoices()[$this->status] ?? $this->status;
    }

    public function formattedRange(): string
    {
        $start = $this->starts_at->timezone(config('app.timezone'));
        $end = $this->ends_at->timezone(config('app.timezone'));

        if ($start->isSameDay($end)) {
            return $start->format('d/m/Y').' · '.$start->format('H:i').' – '.$end->format('H:i');
        }

        return $start->format('d/m/Y H:i').' – '.$end->format('d/m/Y H:i');
    }
}
