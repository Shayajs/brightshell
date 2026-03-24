<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const CATEGORY_EMAIL_CONFIRMATION = 'email_confirmation';

    public const CATEGORY_INFORMATION = 'information';

    public const CATEGORY_RECLAMATION = 'reclamation';

    public const CATEGORY_SUPPORT_ISSUE = 'support_issue';

    public const CATEGORY_API = 'api';

    public const CATEGORY_OTHER = 'other';

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'company_id',
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

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<ProjectRequest, $this> */
    public function projectRequests(): HasMany
    {
        return $this->hasMany(ProjectRequest::class, 'support_ticket_id');
    }

    /**
     * @return array<string, string>
     */
    public static function portalCategoryChoices(): array
    {
        return [
            self::CATEGORY_INFORMATION => 'Information',
            self::CATEGORY_RECLAMATION => 'Réclamation',
            self::CATEGORY_SUPPORT_ISSUE => 'Demande liée à un problème',
            self::CATEGORY_API => 'API',
            self::CATEGORY_OTHER => 'Autre',
        ];
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_EMAIL_CONFIRMATION => 'Confirmation e-mail',
            self::CATEGORY_INFORMATION => 'Information',
            self::CATEGORY_RECLAMATION => 'Réclamation',
            self::CATEGORY_SUPPORT_ISSUE => 'Demande liée à un problème',
            self::CATEGORY_API => 'API',
            self::CATEGORY_OTHER => 'Autre',
            default => $category,
        };
    }
}
