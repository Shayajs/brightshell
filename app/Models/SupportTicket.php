<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    public const CATEGORY_EMAIL_CONFIRMATION = 'email_confirmation';

    public const CATEGORY_OTHER = 'other';

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'email',
        'category',
        'subject',
        'body',
        'status',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
