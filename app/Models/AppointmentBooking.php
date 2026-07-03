<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentBooking extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'appointment_slot_id',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'message',
        'ip',
        'user_agent',
    ];

    /** @return BelongsTo<AppointmentSlot, $this> */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(AppointmentSlot::class, 'appointment_slot_id');
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /** @return array<string, string> */
    public static function statusChoices(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmé',
            self::STATUS_CANCELLED => 'Annulé',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusChoices()[$this->status] ?? $this->status;
    }
}
