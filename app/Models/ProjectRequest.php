<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequest extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'project_id', 'user_id', 'title', 'body', 'status', 'support_ticket_id',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<SupportTicket, $this> */
    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => 'Ouverte',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_DONE => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
        ];
    }
}
