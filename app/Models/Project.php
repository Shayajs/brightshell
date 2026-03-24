<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'archived_at',
        'company_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project): void {
            $base = Str::slug((string) $project->name) ?: 'projet';
            if ($project->slug === null || $project->slug === '') {
                $slug = $base;
                $n = 0;
                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $n++;
                    $slug = $base.'-'.$n;
                }
                $project->slug = $slug;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['can_view', 'can_modify', 'can_annotate', 'can_download'])
            ->withTimestamps();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Projets visibles pour un non-admin (au moins une ligne pivot avec can_view).
     *
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeAccessibleByNonAdmin(Builder $query, User $user): Builder
    {
        return $query->whereHas('members', function (Builder $q) use ($user): void {
            $q->where('users.id', $user->id)
                ->where('project_user.can_view', true);
        });
    }

    /**
     * Liste pour la navigation / dashboard : projets accessibles au sens métier.
     *
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return $query;
        }

        return $query->accessibleByNonAdmin($user);
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN archived_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('name');
    }

    /**
     * Pivot pour un utilisateur (null si pas membre).
     */
    public function membershipFor(User $user): ?object
    {
        $member = $this->members()->where('users.id', $user->id)->first();

        return $member?->pivot;
    }

    /** @return HasMany<ProjectAppointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(ProjectAppointment::class)->orderByDesc('starts_at');
    }

    /** @return HasMany<ProjectNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class)->orderByDesc('id');
    }

    /** @return HasMany<ProjectKanbanBoard, $this> */
    public function kanbanBoards(): HasMany
    {
        return $this->hasMany(ProjectKanbanBoard::class);
    }

    /** @return HasMany<ProjectDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->whereNull('parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<ProjectDocument, $this> */
    public function allDocuments(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<ProjectSpecSection, $this> */
    public function specSections(): HasMany
    {
        return $this->hasMany(ProjectSpecSection::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<ProjectContract, $this> */
    public function contracts(): HasMany
    {
        return $this->hasMany(ProjectContract::class)->orderByDesc('id');
    }

    /** @return HasMany<ProjectPriceItem, $this> */
    public function priceItems(): HasMany
    {
        return $this->hasMany(ProjectPriceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<ProjectRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(ProjectRequest::class)->orderByDesc('id');
    }
}
