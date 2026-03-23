<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inscription publique (/register)
    |--------------------------------------------------------------------------
    |
    | false par défaut : utilise php artisan admin:init pour le premier admin.
    | Mettez BRIGHTSHELL_REGISTRATION_OPEN=true en dev si besoin.
    |
    */
    'registration_open' => filter_var(env('BRIGHTSHELL_REGISTRATION_OPEN', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Domaines (sous-domaines réservés vs vitrine)
    |--------------------------------------------------------------------------
    */
    'domains' => [
        /** Vide = déduit de l’hôte de APP_URL (sans www.), jamais .fr par défaut */
        'root' => env('BRIGHTSHELL_ROOT_DOMAIN'),
        /** Host complet du portail compte, ex. account.brightshell.fr — vide = account.{root} */
        'account_host' => env('BRIGHTSHELL_ACCOUNT_HOST'),
        /** Host portail admin — vide = admin.{root} */
        'admin_host' => env('BRIGHTSHELL_ADMIN_HOST'),
        /** Hosts portails dédiés — vide = {sub}.{root} */
        'collabs_host' => env('BRIGHTSHELL_COLLABS_HOST'),
        'users_host' => env('BRIGHTSHELL_USERS_HOST'),
        'courses_host' => env('BRIGHTSHELL_COURSES_HOST'),
        'settings_host' => env('BRIGHTSHELL_SETTINGS_HOST'),
        /** Documentation interne — vide = docs.{root} */
        'docs_host' => env('BRIGHTSHELL_DOCS_HOST'),
        /** Hub portail connecté — vide = home.{root} */
        'home_host' => env('BRIGHTSHELL_HOME_HOST'),
        /** API publique lecture seule — vide = api.{root} */
        'api_host' => env('BRIGHTSHELL_API_HOST'),
        /** Sous-domaines qui servent la même vitrine que le site principal (ex. www) */
        'vitrine_subdomains' => array_values(array_filter(array_map('trim', explode(',', env('BRIGHTSHELL_VITRINE_SUBDOMAINS', 'www'))))),
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs des portails (optionnel : sinon schéma APP_URL + sous-domaine)
    |--------------------------------------------------------------------------
    */
    'portals' => [
        'admin_url' => rtrim((string) env('BRIGHTSHELL_PORTAL_ADMIN_URL', ''), '/'),
        'collabs_url' => rtrim((string) env('BRIGHTSHELL_PORTAL_COLLABS_URL', ''), '/'),
        'users_url' => rtrim((string) env('BRIGHTSHELL_PORTAL_USERS_URL', ''), '/'),
        'courses_url' => rtrim((string) env('BRIGHTSHELL_PORTAL_COURSES_URL', ''), '/'),
        'settings_url' => rtrim((string) env('BRIGHTSHELL_PORTAL_SETTINGS_URL', ''), '/'),
        'docs_url' => rtrim((string) env('BRIGHTSHELL_PORTAL_DOCS_URL', ''), '/'),
        'home_url' => rtrim((string) env('BRIGHTSHELL_PORTAL_HOME_URL', ''), '/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Portail compte (connexion / inscription)
    |--------------------------------------------------------------------------
    |
    | Si base_url est vide, les URLs utilisent le host account_host (ou account.{root}).
    | Sinon, base_url + login_path (ex. https://account.brightshell.fr/login).
    |
    */
    'account' => [
        'base_url' => rtrim((string) env('BRIGHTSHELL_ACCOUNT_URL', ''), '/'),
        'login_path' => env('BRIGHTSHELL_ACCOUNT_LOGIN_PATH', '/login'),
        'post_login_path' => env('BRIGHTSHELL_POST_LOGIN_PATH', '/'),
        /** URL absolue après connexion (prioritaire sur post_login_path + base_url) */
        'post_login_url' => env('BRIGHTSHELL_POST_LOGIN_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Support (tickets, contact depuis la page de vérification e-mail)
    |--------------------------------------------------------------------------
    */
    'support_email' => env('BRIGHTSHELL_SUPPORT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Vitrine : favicon et logo principal (OG, JSON-LD, page d’accueil)
    |--------------------------------------------------------------------------
    |
    | Chemins relatifs à public/ (ex. img/mon-favicon.png).
    | Par défaut : les fichiers déjà présents dans public/img — rien à copier au déploiement.
    |
    */
    'brand' => [
        'favicon' => env('BRIGHTSHELL_BRAND_FAVICON', 'img/etoile_sans_fond_contours_fin.png'),
        'site_logo' => env('BRIGHTSHELL_BRAND_SITE_LOGO', 'img/logo_sans_fond_contours_epais.webp'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates e-mail (layout par défaut, fusionné avec base.layout.json)
    |--------------------------------------------------------------------------
    |
    | Couleurs alignées sur resources/css/app.css (:root vitrine).
    | logoUrl est injecté automatiquement depuis brand.site_logo (BrightshellBrand).
    |
    */
    'mail_layout' => [
        'brand' => [
            'name' => env('APP_NAME', 'BrightShell'),
            'tagline' => 'Développement web full stack',
        ],
        'theme' => [
            'primaryColor' => '#4a6fa5',
            'backgroundColor' => '#050810',
            'cardColor' => '#0a0e1a',
            'textColor' => '#e8f0f8',
            'mutedTextColor' => '#a8b8d8',
            'buttonTextColor' => '#ffffff',
            'dividerColor' => '#2a3550',
        ],
        'footer' => [
            'signature' => 'L’équipe BrightShell',
            'legal' => 'Ce message est envoyé automatiquement.',
        ],
    ],

];
