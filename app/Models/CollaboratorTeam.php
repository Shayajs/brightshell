<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollaboratorTeam extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'is_admin_team',
    ];

    protected function casts(): array
    {
        return [
            'is_admin_team' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'collaborator_team_user')
            ->withPivot('is_team_manager')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<CollaboratorCapability, $this>
     */
    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(
            CollaboratorCapability::class,
            'collaborator_team_capability',
            'collaborator_team_id',
            'collaborator_capability_id'
        );
    }

    /**
     * @return HasMany<CollaboratorTeamMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(CollaboratorTeamMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public static function adminTeam(): ?self
    {
        return static::query()->where('is_admin_team', true)->first();
    }

    public function userPivot(User $user): ?object
    {
        return $this->users()->where('users.id', $user->id)->first()?->pivot;
    }

    public function hasMember(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    public function memberIsTeamManager(User $user): bool
    {
        $row = $this->users()->where('users.id', $user->id)->first();

        return $row !== null && (bool) ($row->pivot->is_team_manager ?? false);
    }
}
