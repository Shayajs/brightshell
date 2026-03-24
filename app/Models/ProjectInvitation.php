<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectInvitation extends Model
{
    protected $fillable = [
        'project_id',
        'email',
        'token',
        'invited_by_user_id',
        'can_view',
        'can_modify',
        'can_annotate',
        'can_download',
        'accepted_at',
        'expires_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProjectInvitation $invitation): void {
            if ($invitation->token === null || $invitation->token === '') {
                $invitation->token = Str::random(48);
            }
            $invitation->email = strtolower(trim((string) $invitation->email));
        });
    }

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_modify' => 'boolean',
            'can_annotate' => 'boolean',
            'can_download' => 'boolean',
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
