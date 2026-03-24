<?php

/**
 * Métadonnées des endpoints publics (clé = nom de route Laravel).
 * Les routes réelles sont dans routes/api-public.php ; complétez ce fichier lorsque vous en ajoutez.
 *
 * L’API privée Sanctum (préfixe /v1, auth Bearer) est documentée dans docs/API_PRIVEE_V1.md.
 */
return [
    'endpoints' => [
        'api.public.v1.entreprise' => [
            'title' => 'Fiche entreprise (JSON)',
            'summary' => 'Données publiques de la fiche entreprise (nom, contact, options d’affichage SIRET / adresse, etc.). Pas de champs internes.',
            'format' => 'application/json',
            'cache' => 'Cache-Control: public, max-age=60',
        ],
    ],
];
