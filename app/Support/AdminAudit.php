<?php

namespace App\Support;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class AdminAudit
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $action, ?Model $subject = null, array $properties = []): void
    {
        $actor = Auth::user();
        if (! $actor instanceof User || (! $actor->isAdmin() && ! $actor->hasRole('admin'))) {
            return;
        }

        AdminAuditLog::query()->create([
            'actor_id' => $actor->id,
            'action' => $action,
            'subject_type' => $subject !== null ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'ip' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 2000) ?: null,
            'created_at' => now(),
        ]);
    }
}
