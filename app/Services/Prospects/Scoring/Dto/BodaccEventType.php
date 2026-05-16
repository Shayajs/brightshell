<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring\Dto;

/**
 * Type métier d'un acte BODACC, normalisé pour le scoring.
 */
enum BodaccEventType: string
{
    case Demenagement = 'demenagement';
    case AugmentationCapital = 'augmentation_capital';
    case Fusion = 'fusion';
    case ChangementDirigeant = 'changement_dirigeant';
    case Creation = 'creation';
    case Vente = 'vente';
    case DepotComptes = 'depot_comptes';
    case ProcedureCollective = 'procedure_collective'; // RJ, Sauvegarde, Liquidation
    case Autre = 'autre';

    /**
     * Mots-clés (case-insensitive, sans accents) trouvés dans le libellé BODACC.
     *
     * @return array<string, list<string>>
     */
    public static function keywordMap(): array
    {
        return [
            self::ProcedureCollective->value => ['redressement', 'liquidation', 'sauvegarde', 'cessation des paiements'],
            self::AugmentationCapital->value => ['augmentation de capital', 'augmentation du capital'],
            self::Demenagement->value => ['transfert du siege', 'transfert de siege', 'changement d adresse', 'nouvelle adresse'],
            self::Fusion->value => ['fusion', 'absorption', 'projet de fusion'],
            self::ChangementDirigeant->value => ['nomination', 'changement de gerant', 'changement de president', 'changement de directeur', 'changement de dirigeant'],
            self::Creation->value => ['immatriculation', 'creation', 'constitution'],
            self::Vente->value => ['vente', 'cession de fonds'],
            self::DepotComptes->value => ['depot des comptes', 'depot de comptes'],
        ];
    }

    /**
     * Détecte le type d'événement à partir d'un libellé brut (ASCII-fold + minuscules).
     */
    public static function detectFromLibelle(string $libelle): self
    {
        $normalized = self::normalize($libelle);

        foreach (self::keywordMap() as $type => $keywords) {
            foreach ($keywords as $needle) {
                if (str_contains($normalized, $needle)) {
                    return self::from($type);
                }
            }
        }

        return self::Autre;
    }

    private static function normalize(string $s): string
    {
        $s = mb_strtolower($s);
        $s = strtr($s, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', '’' => ' ', "'" => ' ',
        ]);

        return $s;
    }
}
