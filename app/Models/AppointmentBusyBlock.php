<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentBusyBlock extends Model
{
    protected $fillable = [
        'starts_at',
        'ends_at',
        'title',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
