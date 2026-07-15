<?php

namespace App\Services\BrightShield;

use App\Models\User;

final class UserClaimsBuilder
{
    /**
     * @param  list<string>  $scopes
     * @return array<string, mixed>
     */
    public function build(User $user, array $scopes): array
    {
        $claims = [
            'sub' => (string) $user->getAuthIdentifier(),
        ];

        if (in_array('email', $scopes, true)) {
            $claims['email'] = $user->email;
            $claims['email_verified'] = $user->hasVerifiedEmail();
        }

        if (in_array('profile', $scopes, true)) {
            $claims['given_name'] = $user->first_name;
            $claims['family_name'] = $user->last_name;
            $claims['name'] = trim($user->first_name.' '.$user->last_name) ?: $user->name;
            $claims['picture'] = $user->avatarUrl();
        }

        if (in_array('phone', $scopes, true)) {
            $claims['phone_number'] = $user->phone;
        }

        if (in_array('roles', $scopes, true)) {
            $claims['roles'] = $user->roles()->pluck('slug')->values()->all();
            $claims['is_admin'] = $user->isAdmin();
        }

        if (in_array('account', $scopes, true)) {
            $claims['account'] = [
                'created_at' => $user->created_at?->toIso8601String(),
                'current_login_at' => $user->current_login_at?->toIso8601String(),
                'previous_login_at' => $user->previous_login_at?->toIso8601String(),
                'profile_notes' => $user->profile_notes,
            ];
        }

        return $claims;
    }

    /**
     * Aperçu lisible des données partagées, pour l'écran de consentement.
     *
     * @param  list<string>  $scopes
     * @return array<string, list<array{label: string, value: string}>>
     */
    public function preview(User $user, array $scopes): array
    {
        $preview = [];

        $preview['openid'] = [
            ['label' => 'Identifiant BrightShell', 'value' => '#'.$user->getAuthIdentifier()],
        ];

        if (in_array('email', $scopes, true)) {
            $preview['email'] = [
                ['label' => 'Adresse e-mail', 'value' => (string) $user->email],
                ['label' => 'E-mail vérifié', 'value' => $user->hasVerifiedEmail() ? 'Oui' : 'Non'],
            ];
        }

        if (in_array('profile', $scopes, true)) {
            $preview['profile'] = [
                ['label' => 'Nom complet', 'value' => trim($user->first_name.' '.$user->last_name) ?: (string) $user->name],
                ['label' => 'Avatar', 'value' => $user->avatarUrl() !== null ? 'Partagé' : 'Aucun'],
            ];
        }

        if (in_array('phone', $scopes, true)) {
            $preview['phone'] = [
                ['label' => 'Téléphone', 'value' => (string) ($user->phone ?: 'Non renseigné')],
            ];
        }

        if (in_array('roles', $scopes, true)) {
            $roles = $user->roles()->pluck('label')->values()->all();
            $preview['roles'] = [
                ['label' => 'Rôles', 'value' => $roles !== [] ? implode(', ', $roles) : 'Aucun'],
            ];
        }

        if (in_array('account', $scopes, true)) {
            $preview['account'] = [
                ['label' => 'Compte créé le', 'value' => (string) $user->created_at?->translatedFormat('j F Y')],
                ['label' => 'Dernière connexion', 'value' => (string) ($user->current_login_at?->translatedFormat('j F Y, H:i') ?? 'Inconnue')],
            ];
        }

        return $preview;
    }
}
