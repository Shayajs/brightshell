<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactMessage extends Model
{
    public const TYPE_GENERAL = 'general';

    public const TYPE_PROFESSIONAL = 'professional';

    public const TYPE_COMPLAINT = 'complaint';

    public const TYPE_PROJECT = 'project';

    public const STATUS_OPEN = 'open';

    public const STATUS_READ = 'read';

    public const STATUS_REPLIED = 'replied';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'subject',
        'reference',
        'project_title',
        'project_kind',
        'budget_range',
        'deadline',
        'body',
        'body_html',
        'ip',
        'user_agent',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ContactAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(ContactAttachment::class);
    }

    /**
     * @param  Builder<ContactMessage>  $query
     * @return Builder<ContactMessage>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * @param  Builder<ContactMessage>  $query
     * @return Builder<ContactMessage>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /** @return array<string, string> */
    public static function typeChoices(): array
    {
        return [
            self::TYPE_GENERAL => 'Curiosité',
            self::TYPE_PROFESSIONAL => 'Projet professionnel',
            self::TYPE_COMPLAINT => 'Réclamation',
            self::TYPE_PROJECT => 'Soumettre un projet',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return self::typeChoices()[$type] ?? $type;
    }

    /** @return array<string, string> */
    public static function statusChoices(): array
    {
        return [
            self::STATUS_OPEN => 'Nouveau',
            self::STATUS_READ => 'Lu',
            self::STATUS_REPLIED => 'Répondu',
            self::STATUS_ARCHIVED => 'Archivé',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::statusChoices()[$status] ?? $status;
    }

    /** @return array<string, string> */
    public static function projectKindChoices(): array
    {
        return [
            'web' => 'Site web',
            'app' => 'Application web',
            'mobile' => 'Application mobile',
            'refonte' => 'Refonte / migration',
            'api' => 'API / intégration',
            'autre' => 'Autre',
        ];
    }

    /** @return array<string, string> */
    public static function budgetChoices(): array
    {
        return [
            '<2k' => 'Moins de 2 000 €',
            '2k-5k' => '2 000 € – 5 000 €',
            '5k-10k' => '5 000 € – 10 000 €',
            '10k-25k' => '10 000 € – 25 000 €',
            '>25k' => 'Plus de 25 000 €',
            'unknown' => 'À définir ensemble',
        ];
    }

    /** @return array<string, string> */
    public static function deadlineChoices(): array
    {
        return [
            'asap' => 'Le plus tôt possible',
            '1-3m' => '1 à 3 mois',
            '3-6m' => '3 à 6 mois',
            '6m+' => 'Plus de 6 mois',
            'flex' => 'Flexible',
        ];
    }
}
