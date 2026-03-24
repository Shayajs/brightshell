<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'first_name',
    'last_name',
    'email',
    'password',
    'is_admin',
    'can_manage_collaborator_team_managers',
    'phone',
    'avatar_path',
    'profile_notes',
    'browser_notifications_enabled',
])]
#[Hidden(['password', 'remember_token', 'email_reverse_verification_token'])]
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, MustVerifyEmail, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->isForceDeleting()) {
                if ($user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                }
                $user->notifications()->delete();

                return;
            }

            if (! str_starts_with((string) $user->email, 'archived+')) {
                $user->archived_email = $user->email;
            }

            $user->email = 'archived+'.$user->id.'.'.time().'@archived.invalid';

            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
                $user->avatar_path = null;
            }

            $user->saveQuietly();

            $user->notifications()->delete();

            DB::table('sessions')->where('user_id', $user->id)->delete();
        });

        static::restoring(function (User $user): void {
            if ($user->archived_email) {
                $user->email = $user->archived_email;
                $user->archived_email = null;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'can_manage_collaborator_team_managers' => 'boolean',
            'browser_notifications_enabled' => 'boolean',
            'current_login_at' => 'datetime',
            'previous_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('can_manage_company')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<CollaboratorTeam, $this>
     */
    public function collaboratorTeams(): BelongsToMany
    {
        return $this->belongsToMany(CollaboratorTeam::class, 'collaborator_team_user')
            ->withPivot('is_team_manager')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot(['can_view', 'can_modify', 'can_annotate', 'can_download'])
            ->withTimestamps();
    }

    public function belongsToCompany(Company $company): bool
    {
        return $this->companies()->where('companies.id', $company->id)->exists();
    }

    /** Client désigné comme responsable de la fiche société (plusieurs sociétés possibles). */
    public function canManageClientCompany(Company $company): bool
    {
        if (! $this->hasRole('client')) {
            return false;
        }

        $row = $this->companies()->where('companies.id', $company->id)->first();

        return $row !== null && (bool) ($row->pivot->can_manage_company ?? false);
    }

    public function avatarUrl(): ?string
    {
        $path = $this->avatar_path;

        if ($path === null || $path === '') {
            return null;
        }

        $path = str_replace('\\', '/', ltrim($path, '/'));

        // URL relative : même origine que la requête (évite APP_URL ≠ domaine gateway / sous-domaine).
        return '/storage/'.$path;
    }

    /** Cours pédagogiques propres à cet utilisateur (élève). */
    /** @return HasMany<StudentCourse, $this> */
    public function studentCourses(): HasMany
    {
        return $this->hasMany(StudentCourse::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Matières / dossiers / fichiers (périmètre élève). */
    /** @return HasMany<StudentSubject, $this> */
    public function studentSubjects(): HasMany
    {
        return $this->hasMany(StudentSubject::class)->orderBy('sort_order')->orderBy('id');
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function isCollaboratorPortalUser(): bool
    {
        return $this->isAdmin() || $this->hasRole('admin') || $this->hasRole('collaborator');
    }

    /**
     * Comptes pouvant accéder au portail collaborateurs.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeCollaboratorPortalUsers(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->where('is_admin', true)
                ->orWhereHas('roles', fn (Builder $r) => $r->whereIn('slug', ['admin', 'collaborator']));
        });
    }

    public function canAssignCollaboratorTeamManagers(): bool
    {
        return $this->isAdmin() || (bool) $this->can_manage_collaborator_team_managers;
    }

    public function belongsToAdminCollaboratorTeam(): bool
    {
        return $this->collaboratorTeams()->where('collaborator_teams.is_admin_team', true)->exists();
    }

    public function hasCollaboratorCapability(string $slug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->collaboratorTeams()
            ->whereHas('capabilities', fn ($q) => $q->where('slug', $slug))
            ->exists();
    }

    public function managesCollaboratorTeam(CollaboratorTeam $team): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $row = $this->collaboratorTeams()->where('collaborator_teams.id', $team->id)->first();

        return $row !== null && (bool) ($row->pivot->is_team_manager ?? false);
    }

    /**
     * Nom complet affiché (concaténation prénom + nom).
     * Conservé pour l’API, l’admin et le code existant.
     */
    public function getNameAttribute(): string
    {
        return trim(trim((string) ($this->attributes['first_name'] ?? '')).' '.trim((string) ($this->attributes['last_name'] ?? '')));
    }

    /**
     * Assignation via une seule chaîne (ex. commandes artisan, tests).
     */
    public function setNameAttribute(?string $value): void
    {
        if ($value === null || trim($value) === '') {
            $this->attributes['first_name'] = '';
            $this->attributes['last_name'] = '';

            return;
        }

        [$first, $last] = self::splitFullName(trim($value));
        $this->attributes['first_name'] = $first;
        $this->attributes['last_name'] = $last;
    }

    /**
     * Premier prénom seul (pour salutations), même si plusieurs prénoms sont stockés dans first_name.
     */
    public function greetingFirstName(): string
    {
        $raw = trim((string) ($this->attributes['first_name'] ?? ''));
        if ($raw === '') {
            $raw = trim((string) ($this->attributes['last_name'] ?? ''));
        }
        if ($raw === '') {
            return '';
        }
        $normalized = preg_replace('/\s+/u', ' ', $raw) ?? $raw;

        return explode(' ', $normalized, 2)[0];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function splitFullName(string $full): array
    {
        $full = trim($full);
        if ($full === '') {
            return ['', ''];
        }
        $p = strpos($full, ' ');
        if ($p === false) {
            return [$full, ''];
        }

        return [substr($full, 0, $p), trim(substr($full, $p + 1))];
    }

    /**
     * Rôle le plus élevé (priority max). Sans rôle en base, retombe sur is_admin → admin.
     */
    public function highestRole(): ?Role
    {
        $role = $this->roles()->orderByDesc('roles.priority')->orderByDesc('roles.id')->first();

        if ($role !== null) {
            return $role;
        }

        if ($this->isAdmin()) {
            return Role::query()->where('slug', 'admin')->first();
        }

        return null;
    }
}
