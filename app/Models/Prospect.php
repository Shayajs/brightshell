<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Prospects\Scoring\ScoreBand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

/**
 * Prospect B2B issu de l'import multi-sources (Recherche Entreprises + BODACC + BAN + INPI).
 *
 * Scoring porté par {@see \App\Services\Prospects\Scoring\ScoreEngine} :
 * - score_global : score final (peut dépasser 100 via multiplicateurs)
 * - score_website / score_software : scores différenciés par cible commerciale
 * - score_band : Hot | Priority | Standard | Watch | Excluded
 * - score_breakdown : JSON explicatif (contributions par pôle + multiplicateurs déclenchés)
 */
#[Fillable([
    'siren', 'siret',
    'nom_entreprise', 'nom_dirigeant', 'prenom_dirigeant',
    'date_naissance_dirigeant', 'date_nomination_dirigeant',
    'code_naf', 'libelle_naf', 'nature_juridique', 'tranche_effectif',
    'nombre_etablissements', 'site_internet', 'email_contact', 'telephone',
    'adresse', 'code_postal', 'ville', 'departement', 'region',
    'code_insee_commune', 'latitude', 'longitude', 'distance_km_home',
    'date_creation', 'date_dernier_demenagement',
    'domaine_web', 'logo_url',
    'chiffre_affaires', 'chiffre_affaires_n_moins_1', 'resultat_net', 'exercice_bilan',
    'score_global', 'score_website', 'score_software', 'score_band',
    'niveau_interet', 'score_breakdown', 'score_confidence',
    'raw_payload', 'bodacc_events', 'procedure_collective',
    'traite', 'traite_at', 'notes', 'scored_at',
])]
class Prospect extends Model
{
    protected $casts = [
        'date_naissance_dirigeant' => 'date',
        'date_nomination_dirigeant' => 'date',
        'date_creation' => 'date',
        'date_dernier_demenagement' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_km_home' => 'integer',
        'nombre_etablissements' => 'integer',
        'chiffre_affaires' => 'integer',
        'chiffre_affaires_n_moins_1' => 'integer',
        'resultat_net' => 'integer',
        'exercice_bilan' => 'integer',
        'score_global' => 'integer',
        'score_website' => 'integer',
        'score_software' => 'integer',
        'niveau_interet' => 'integer',
        'score_confidence' => 'integer',
        'score_breakdown' => 'array',
        'raw_payload' => 'array',
        'bodacc_events' => 'array',
        'procedure_collective' => 'boolean',
        'traite' => 'boolean',
        'traite_at' => 'datetime',
        'scored_at' => 'datetime',
    ];

    /**
     * Bande de score sous forme d'enum typé.
     */
    public function band(): ScoreBand
    {
        return ScoreBand::tryFrom((string) $this->score_band) ?? ScoreBand::Watch;
    }

    /**
     * Initiales pour fallback logo (ex. "Atelier Industriel" → "AI").
     */
    public function getInitialesAttribute(): string
    {
        $words = preg_split('/\s+/u', trim((string) $this->nom_entreprise)) ?: [];
        $first = mb_substr($words[0] ?? '?', 0, 1);
        $second = isset($words[1]) ? mb_substr($words[1], 0, 1) : '';

        return mb_strtoupper($first.$second);
    }

    /**
     * URL Clearbit déduite du domaine. Null si on n'a pas de domaine — la vue fallback sur initiales.
     */
    public function getLogoClearbitUrlAttribute(): ?string
    {
        if (empty($this->domaine_web)) {
            return null;
        }

        return 'https://logo.clearbit.com/'.$this->domaine_web.'?size=80';
    }

    /**
     * URL de recherche LinkedIn pour qualifier rapidement le dirigeant.
     */
    public function getLinkedinSearchUrlAttribute(): ?string
    {
        if (empty($this->nom_dirigeant)) {
            return null;
        }

        $query = trim(($this->prenom_dirigeant ?? '').' '.$this->nom_dirigeant.' '.$this->nom_entreprise);

        return 'https://www.linkedin.com/search/results/people/?keywords='.urlencode($query);
    }

    /**
     * URL fiche officielle annuaire-entreprises.data.gouv.fr.
     */
    public function getFicheGouvUrlAttribute(): string
    {
        return 'https://annuaire-entreprises.data.gouv.fr/entreprise/'.$this->siren;
    }

    public function getAgeAnneesAttribute(): ?int
    {
        return $this->date_creation !== null ? (int) $this->date_creation->diffInYears(now()) : null;
    }

    public function getAgeDirigeantAnneesAttribute(): ?int
    {
        return $this->date_naissance_dirigeant !== null ? (int) $this->date_naissance_dirigeant->diffInYears(now()) : null;
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeBand(Builder $query, string|ScoreBand $band): Builder
    {
        $value = $band instanceof ScoreBand ? $band->value : $band;

        return $query->where('score_band', $value);
    }

    public function scopeNiveau(Builder $query, int $niveau): Builder
    {
        return $query->where('niveau_interet', $niveau);
    }

    public function scopeNonTraites(Builder $query): Builder
    {
        return $query->where('traite', false);
    }

    public function scopeHot(Builder $query): Builder
    {
        return $query->where('score_band', ScoreBand::Hot->value);
    }

    public function scopeParDepartement(Builder $query, string $dep): Builder
    {
        return $query->where('departement', $dep);
    }

    public function scopeWithoutExcluded(Builder $query): Builder
    {
        return $query->where('score_band', '!=', ScoreBand::Excluded->value);
    }

    /**
     * Filtre Haversine : prospects à <= $km du point (lat, long).
     */
    public function scopeWithinKm(Builder $query, float $lat, float $long, int $km): Builder
    {
        $haversine = sprintf(
            '(6371 * ACOS(COS(RADIANS(%F)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(%F)) + SIN(RADIANS(%F)) * SIN(RADIANS(latitude))))',
            $lat, $long, $lat
        );

        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("$haversine <= ?", [$km]);
    }
}
