<?php

/**
 * Métadonnées des endpoints publics (clé = nom de route Laravel).
 * Les routes réelles sont dans routes/api-public.php ; complète ce fichier quand tu en ajoutes.
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
