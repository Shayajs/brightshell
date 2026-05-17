<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Sortie CSV / stockage
    |--------------------------------------------------------------------------
    */
    'csv_disk' => env('PROSPECTS_CSV_DISK', 'local'),
    'csv_directory' => 'prospects',

    /*
    |--------------------------------------------------------------------------
    | Point de référence géographique (pour le bonus de proximité Haversine)
    |--------------------------------------------------------------------------
    */
    'home' => [
        'lat' => env('BRIGHTSHELL_PROSPECTS_HOME_LAT'),
        'long' => env('BRIGHTSHELL_PROSPECTS_HOME_LONG'),
        'radius_km' => (int) env('BRIGHTSHELL_PROSPECTS_HOME_RADIUS_KM', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs API publiques data.gouv.fr
    |--------------------------------------------------------------------------
    | Recherche Entreprises : le sous-domaine *.api.data.gouv.fr n'est pas résolu
    | partout ; le canonique est recherche-entreprises.api.gouv.fr.
    */
    'api' => [
        'recherche_entreprises' => env(
            'BRIGHTSHELL_PROSPECTS_RECHERCHE_ENTREPRISES_URL',
            'https://recherche-entreprises.api.gouv.fr',
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Clients API — activation conditionnelle (Bearer optionnels)
    |--------------------------------------------------------------------------
    */
    'enrichment' => [
        'insee_token' => env('INSEE_TOKEN'),
        'inpi_token' => env('INPI_TOKEN'),
        'insee_enabled' => ! empty(env('INSEE_TOKEN')),
        'inpi_enabled' => ! empty(env('INPI_TOKEN')),
        // INPI : volume coûteux → on n'enrichit que les prospects band ≥ priority.
        'inpi_min_band' => 'priority',
        // Cache BAN : 30 jours (les adresses changent peu).
        'geocoding_cache_ttl_days' => 30,
        // Cache Géo API (régions/dépts/communes) : 7 jours.
        'geo_api_cache_ttl_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Throttle (rate limiting) — micro-secondes entre 2 appels par client
    |--------------------------------------------------------------------------
    | API publique Recherche Entreprises : 7 req/s → 150 ms.
    | BAN : 50 req/s → 25 ms.
    | BODACC : tolérant → 300 ms.
    | INSEE : 30 req/min → 2 100 ms.
    | INPI : conservateur → 800 ms.
    */
    'throttle_us' => [
        'recherche_entreprises' => 150_000,
        'bodacc' => 300_000,
        'adresse' => 25_000,
        'geo' => 25_000,
        'insee' => 2_100_000,
        'inpi' => 800_000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring — Couche A : Points bruts (/100)
    |--------------------------------------------------------------------------
    */
    'scoring' => [

        // ───────────────── A. Secteur (Code NAF) — 30 pts max ─────────────────
        'naf' => [
            'ideal_prefixes' => [
                // Industrie manufacturière
                '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
                '20', '21', '22', '23', '24', '25', '26', '27', '28', '29',
                '30', '31', '32', '33',
                // Négoce / commerce de gros
                '46',
                // Logistique / transport
                '49', '50', '51', '52', '53',
                // Services IT B2B
                '62', '63',
                // Ingénierie / conseil B2B
                '70', '71', '72', '73', '74',
            ],
            'intermediate_prefixes' => [
                '43',     // Bâtiment second œuvre
                '45.20',  // Mécanique auto pro
                '45.31',
                '95',     // Réparation
            ],
            'excluded_prefixes' => [
                '01', '02', '03', // Agriculture
                '47',             // Commerce de détail
                '55', '56',       // HCR
                '64.20',          // Holdings
                '68',             // SCI / immobilier
                '84',             // Administration publique
                '94',             // Associations
            ],
            'points_ideal' => 30,
            'points_intermediate' => 15,
            'points_excluded' => 0,
            'points_default' => 8,
        ],

        // ───────────────── B. Capacité structurelle — 30 pts max ─────────────
        'effectif' => [
            // INSEE : 00=0, 01=1-2, 02=3-5, 03=6-9, 11=10-19, 12=20-49, 21=50-99…
            'sweet_spot' => ['11', '12'],            // 10-49 : meilleur ROI
            'sweet_spot_points' => 15,
            'small' => ['01', '02', '03'],           // 1-9
            'small_points' => 10,
            'large' => ['21', '22', '31', '32'],     // 50+
            'large_points' => 12,
            'zero' => ['00', 'NN', ''],
            'zero_points' => 0,
        ],
        'age_entreprise' => [
            // Min/max en années (incluses), points associés.
            'bands' => [
                ['min' => 4,  'max' => 10,  'points' => 15],
                ['min' => 10, 'max' => 25,  'points' => 10],
                ['min' => 25, 'max' => 200, 'points' => 8],
                ['min' => 2,  'max' => 4,   'points' => 7],
                ['min' => 0,  'max' => 2,   'points' => 5],
            ],
        ],

        // ───────────────── C. Gouvernance (âge dirigeant) — 15 pts max ───────
        'gouvernance' => [
            'bands' => [
                ['min' => 25, 'max' => 45, 'points' => 15],
                ['min' => 46, 'max' => 55, 'points' => 10],
                ['min' => 56, 'max' => 60, 'points' => 5],
                ['min' => 61, 'max' => 150, 'points' => 0],
            ],
        ],

        // ───────────────── D. Signaux BODACC — 25 pts max ────────────────────
        'signaux' => [
            // Demi-vie d'un signal en mois : décroissance exponentielle e^(-Δm/decay).
            'decay_months' => 12,
            'cap' => 25,
            'points' => [
                'demenagement' => 10,
                'augmentation_capital' => 15,
                'fusion' => 12,
                'changement_dirigeant' => 8,
                'creation' => 6,
            ],
        ],

        // ───────────────── Confidence (couverture des données) ───────────────
        'confidence_weights' => [
            'naf' => 25,
            'effectif' => 20,
            'age_dirigeant' => 15,
            'bodacc' => 20,
            'finances' => 20,
        ],

        // ───────────────── Bandes finales ────────────────────────────────────
        'bands' => [
            'hot' => 120,        // ≥ 120 — Appel immédiat
            'priority' => 80,    // 80..119
            'standard' => 50,    // 50..79
            'watch' => 25,       // 25..49 — Veille
            // < 25 = excluded
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Détecteurs de besoins (signaux indépendants, bonus secs)
    |--------------------------------------------------------------------------
    | Chaque besoin émerge tout seul selon les données collectées. Plusieurs
    | besoins peuvent se cumuler sur le même prospect — c'est leur somme qui
    | détermine le score différencié `score_website` / `score_software`.
    |
    | Format : 'key' => ['points' => int, 'targets' => ['website'|'software'|'global'|'*']]
    | Mettre 'points' = 0 pour désactiver un besoin sans toucher au code.
    */
    'needs' => [
        // ─── Site web ────────────────────────────────────────────────────────
        'website_absent' => [
            'points' => 25,
            'targets' => ['website', 'global'],
            'label' => 'Aucun site web déclaré',
        ],
        'website_dead' => [
            'points' => 20,
            'targets' => ['website', 'global'],
            'label' => 'Site déclaré injoignable (404 / 5xx)',
        ],
        'website_outdated_copyright' => [
            'points' => 15,
            'targets' => ['website'],
            'label' => 'Copyright du site daté de plus de 3 ans',
            'min_age_years' => 3,
        ],
        'website_non_responsive' => [
            'points' => 12,
            'targets' => ['website'],
            'label' => 'Site non responsive (pas de meta viewport)',
        ],
        'website_no_https' => [
            'points' => 10,
            'targets' => ['website'],
            'label' => 'Site sans HTTPS (refonte / sécurité)',
        ],
        'website_legacy_builder' => [
            'points' => 8,
            'targets' => ['website'],
            'label' => 'Site sur un constructeur historique (Wix Classic, Jimdo, Site123, e-monsite…)',
        ],
        'email_generique' => [
            'points' => 8,
            'targets' => ['website'],
            'label' => 'Email de contact générique (gmail, free, orange…)',
        ],

        // ─── Logiciel / outils internes ──────────────────────────────────────
        'effectif_eleve_b2b' => [
            'points' => 20,
            'targets' => ['software'],
            'label' => 'Effectif élevé en secteur B2B (besoin ERP / CRM)',
            'min_effectif_code' => '12', // 20-49 salariés
        ],
        'multi_etablissements' => [
            'points' => 15,
            'targets' => ['software'],
            'label' => 'Plusieurs établissements (gestion multi-sites)',
            'min_etablissements' => 3,
        ],
        'negoce_sans_catalogue' => [
            'points' => 12,
            'targets' => ['software', 'website'],
            'label' => 'Négoce / commerce de gros sans site marchand (catalogue B2B)',
        ],
        'industrie_traceabilite' => [
            'points' => 10,
            'targets' => ['software'],
            'label' => 'Industrie manufacturière (suivi production, traçabilité, MES)',
        ],
        'ca_eleve_sans_finances' => [
            'points' => 8,
            'targets' => ['software'],
            'label' => 'Effectif moyen avec besoin d’automatisation comptable/RH',
        ],

        // ─── Signal transverse (web + soft) ──────────────────────────────────
        'croissance_recente' => [
            'points' => 8,
            'targets' => ['*'],
            'label' => 'Croissance récente (augmentation de capital + déménagement)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Probe HTTP du site web
    |--------------------------------------------------------------------------
    */
    'website_probe' => [
        'enabled' => true,
        'timeout_seconds' => 5,
        'cache_ttl_days' => 30,
        'max_bytes' => 65_536,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring — Couche B : Multiplicateurs non linéaires
    |--------------------------------------------------------------------------
    */
    'modifiers' => [
        'relais_generationnel' => [
            'min_age_entreprise' => 20,
            'max_age_dirigeant' => 40,
            'fenetre_nomination_mois' => 12,
            'multiplier' => 1.5,
        ],
        'zero_salarie_industriel' => [
            'fenetre_acte_mois' => 12,
            'multiplier' => 1.2,
        ],
        'usine_a_gaz_humaine' => [
            'min_effectif' => 20,
            // Variation CA stagnante : entre -5% et +2%
            'ca_variation_min' => -0.05,
            'ca_variation_max' => 0.02,
            'multiplier' => 1.3,
        ],
        'momentum_signaux' => [
            'min_evenements' => 2,
            'fenetre_mois' => 6,
            'multiplier' => 1.15,
        ],
        'digital_gap' => [
            'emails_generiques' => [
                'gmail.com', 'yahoo.com', 'yahoo.fr', 'orange.fr', 'wanadoo.fr',
                'free.fr', 'hotmail.com', 'hotmail.fr', 'outlook.com', 'outlook.fr',
                'laposte.net', 'sfr.fr', 'live.fr', 'live.com',
            ],
            'bonus_points_website' => 10,
            'bonus_points_global' => 5,
        ],
        'hub_local' => [
            'min_etablissements' => 3,
            'bonus_points' => 5,
        ],
        'proximite_geographique' => [
            // Bonus pondéré : décroît linéairement de bonus_max (à 0 km) à 0 (à radius_km).
            'bonus_max' => 5,
        ],
    ],

];
