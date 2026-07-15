<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Émetteur OIDC (issuer)
    |--------------------------------------------------------------------------
    */
    'issuer' => env('BRIGHTSHIELD_ISSUER'),

    /*
    |--------------------------------------------------------------------------
    | Durées de vie des jetons
    |--------------------------------------------------------------------------
    */
    'access_token_ttl_minutes' => (int) env('BRIGHTSHIELD_ACCESS_TOKEN_TTL', 60),
    'refresh_token_ttl_days' => (int) env('BRIGHTSHIELD_REFRESH_TOKEN_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Scopes OAuth2 / OIDC
    |--------------------------------------------------------------------------
    */
    'scopes' => [
        'openid' => 'Identifiant BrightShell (OIDC)',
        'profile' => 'Prénom, nom et avatar',
        'email' => 'Adresse e-mail et statut de vérification',
        'phone' => 'Numéro de téléphone',
        'roles' => 'Rôles BrightShell (client, élève, collaborateur…)',
        'account' => 'Informations complètes du compte (date de création, dernière connexion, notes de profil)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Applications clientes pré-enregistrées (v1)
    |--------------------------------------------------------------------------
    |
    | Utilisé par brightshield:register-client et les tests.
    |
    */
    'clients' => [
        'futurmeal' => [
            'name' => 'Futurmeal',
            'redirect_uris' => array_values(array_filter(array_map('trim', explode(',', env(
                'BRIGHTSHIELD_FUTURMEAL_REDIRECT_URIS',
                'http://futurmeal.test/auth/brightshield/callback,https://futurmeal.test/auth/brightshield/callback'
            ))))),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Métadonnées affichées sur l'écran de consentement
    |--------------------------------------------------------------------------
    */
    'client_labels' => [
        'futurmeal' => [
            'title' => 'Futurmeal',
            'description' => 'Planification de repas et suivi nutritionnel.',
            /** Fallback si l’app n’envoie pas ?app_icon= sur /oauth/authorize */
            'icon_url' => env('BRIGHTSHIELD_FUTURMEAL_ICON_URL', 'http://futurmeal.test/apple-touch-icon.png'),
            /** Hôtes supplémentaires autorisés pour l’icône (en plus des redirect_uris) */
            'icon_hosts' => array_values(array_filter(array_map('trim', explode(',', env('BRIGHTSHIELD_FUTURMEAL_ICON_HOSTS', 'futurmeal.test,futurmeal.pp.ua,www.futurmeal.pp.ua,futurmeal.fr,www.futurmeal.fr'))))),
        ],
    ],

];
