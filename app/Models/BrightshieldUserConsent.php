<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class BrightshieldUserConsent extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'client_id',
        'scopes',
        'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'granted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  list<\Laravel\Passport\Scope>|array<int, \Laravel\Passport\Scope>  $scopes
     */
    public static function hasGranted(User $user, string $clientId, array $scopes): bool
    {
        $consent = static::query()
            ->where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->first();

        if ($consent === null) {
            return false;
        }

        if ($scopes === []) {
            return true;
        }

        $granted = collect($consent->scopes ?? []);

        return collect($scopes)->pluck('id')->diff($granted)->isEmpty();
    }

    /**
     * @param  list<string>  $scopeIds
     */
    public static function record(User $user, string $clientId, array $scopeIds): void
    {
        static::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'client_id' => $clientId,
            ],
            [
                'scopes' => array_values(array_unique($scopeIds)),
                'granted_at' => now(),
            ],
        );
    }

    public static function revoke(User $user, string $clientId): void
    {
        static::query()
            ->where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->delete();
    }

    /**
     * @return Collection<int, BrightshieldUserConsent>
     */
    public static function forUser(User $user): Collection
    {
        return static::query()
            ->where('user_id', $user->id)
            ->orderByDesc('granted_at')
            ->get();
    }
}
