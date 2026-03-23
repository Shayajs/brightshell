<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaboratorTeamMessage extends Model
{
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (CollaboratorTeamMessage $message): void {
            if ($message->created_at === null) {
                $message->created_at = now();
            }
        });
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'collaborator_team_id',
        'user_id',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<CollaboratorTeam, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(CollaboratorTeam::class, 'collaborator_team_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
