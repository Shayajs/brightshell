<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisioParticipant extends Model
{
    protected $fillable = [
        'visio_room_id',
        'user_id',
        'guest_name',
        'joined_at',
        'left_at',
        'is_presenter',
        'connection_meta',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'is_presenter' => 'boolean',
            'connection_meta' => 'array',
        ];
    }

    /** @return BelongsTo<VisioRoom, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(VisioRoom::class, 'visio_room_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
