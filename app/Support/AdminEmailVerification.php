<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

/**
 * Les comptes autorisés sur le portail admin (rôle admin ou flag is_admin)
 * ont l’e-mail considéré comme confirmé : on l’active si ce n’était pas encore le cas.
 */
final class AdminEmailVerification
{
    public static function ensureVerifiedIfAdmin(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (! $user->hasRole('admin') && ! $user->isAdmin()) {
            return;
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
    }
}
