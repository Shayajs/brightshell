<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'is_admin',
    'phone',
    'profile_notes',
    'browser_notifications_enabled',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        return $this->belongsToMany(Company::class);
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
