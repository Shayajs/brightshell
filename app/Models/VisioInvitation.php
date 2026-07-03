<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VisioInvitation extends Model
{
    protected $fillable = [
        'visio_room_id',
        'email',
        'token',
        'invited_by_user_id',
        'expires_at',
        'accepted_at',
        'can_join',
        'can_present',
    ];

    protected static function booted(): void
    {
        static::creating(function (VisioInvitation $invitation): void {
            if ($invitation->token === null || $invitation->token === '') {
                $invitation->token = Str::random(48);
            }
            if (is_string($invitation->email) && $invitation->email !== '') {
                $invitation->email = strtolower(trim($invitation->email));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'can_join' => 'boolean',
            'can_present' => 'boolean',
        ];
    }

    /** @return BelongsTo<VisioRoom, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(VisioRoom::class, 'visio_room_id');
    }

    /** @return BelongsTo<User, $this> */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
