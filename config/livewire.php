<?php

return [

    'class_namespace' => 'App\\Livewire',

    'view_path' => resource_path('views/livewire'),

    'layout' => 'components.layouts.app',

    'lazy_placeholder' => null,

    'temporary_file_upload' => [
        'disk' => null,
        'rules' => null,
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],

    'render_on_redirect' => false,

    'legacy_model_binding' => false,

    /*
    | Désactivé : le layout admin injecte styles/scripts uniquement sur prospects.*
    | (évite doublons + permet assets statiques dans public/vendor/livewire).
    */
    'inject_assets' => false,

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],

    'inject_morph_markers' => true,

    'smart_wire_keys' => false,

    'pagination_theme' => 'tailwind',

    'release_token' => 'a',

    /*
    | URL absolue optionnelle (ex. https://admin.brightshell.fr) si les routes
    | /livewire/* ne répondent pas sur un sous-domaine. Laisser null pour utiliser
    | public/vendor/livewire (recommandé) ou la route dynamique /livewire/livewire.js.
    */
    'asset_url' => env('LIVEWIRE_ASSET_URL'),

];
