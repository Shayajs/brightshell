<?php

namespace App\Support;

/**
 * Résolution des chemins médias du CV (fichiers sous public/).
 */
final class CvMedia
{
    /**
     * Candidats par défaut pour la photo de profil (ordre de priorité).
     *
     * @var list<string>
     */
    private const PROFILE_CANDIDATES = [
        'img/cv.webp',
        'img/cv.png',
        'img/cv.jpg',
        'img/cv.jpeg',
        'image/cv.jpg', // ancien emplacement
    ];

    private const PROFILE_FALLBACK = 'img/logo_silhouette.webp';

    /**
     * URL absolue de la photo de profil (premier fichier existant, sinon silhouette).
     */
    public static function profilePhotoUrl(array $contact): string
    {
        $custom = $contact['etat_civil']['photo'] ?? null;
        if (is_string($custom) && $custom !== '') {
            $url = self::publicAssetUrlIfExists($custom);
            if ($url !== null) {
                return $url;
            }
        }

        foreach (self::PROFILE_CANDIDATES as $relative) {
            $url = self::publicAssetUrlIfExists($relative);
            if ($url !== null) {
                return $url;
            }
        }

        return asset(self::PROFILE_FALLBACK);
    }

    /**
     * URL asset si le fichier existe sous public/, sinon null (pas de balise <img> cassée).
     */
    public static function publicAssetUrlIfExists(?string $relative): ?string
    {
        if ($relative === null || $relative === '') {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        if ($relative === '' || str_contains($relative, '..')) {
            return null;
        }

        $full = public_path($relative);

        return is_file($full) && is_readable($full) ? asset($relative) : null;
    }
}
