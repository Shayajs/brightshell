<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VisioRoom extends Model
{
    protected $fillable = [
        'project_id',
        'host_user_id',
        'slug',
        'title',
        'status',
        'starts_at',
        'ends_at',
        'meta',
    ];

    protected static function booted(): void
    {
        static::creating(function (VisioRoom $room): void {
            if ($room->slug === null || $room->slug === '') {
                $base = Str::slug((string) $room->title) ?: 'visio';
                $slug = $base;
                $i = 0;
                while (static::query()->where('slug', $slug)->exists()) {
                    $i++;
                    $slug = $base.'-'.$i;
                }
                $room->slug = $slug;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function hostUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /** @return HasMany<VisioInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(VisioInvitation::class)->latest('id');
    }

    /** @return HasMany<VisioParticipant, $this> */
    public function participants(): HasMany
    {
        return $this->hasMany(VisioParticipant::class)->latest('id');
    }
}
