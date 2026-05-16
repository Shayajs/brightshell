<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring\Dto;

use Carbon\CarbonImmutable;

/**
 * Snapshot brut d'une entreprise normalisée pour le moteur de scoring.
 *
 * Hydraté par ImportProspectsAction depuis :
 * - Recherche Entreprises (data.gouv.fr) — données socle
 * - BODACC (signaux faibles)
 * - BAN (géocodage)
 * - INPI (bilans complets, optionnel)
 */
final readonly class ProspectInput
{
    public function __construct(
        public string $siren,
        public ?string $nomEntreprise = null,
        public ?string $codeNaf = null,
        public ?string $natureJuridique = null,
        public ?string $trancheEffectif = null,
        public ?CarbonImmutable $dateCreation = null,
        public ?CarbonImmutable $dateNaissanceDirigeant = null,
        public ?CarbonImmutable $dateNominationDirigeant = null,
        public ?string $siteInternet = null,
        public ?string $emailContact = null,
        public ?int $chiffreAffairesN = null,
        public ?int $chiffreAffairesNm1 = null,
        public ?int $resultatNet = null,
        public int $nombreEtablissements = 1,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?int $distanceKmHome = null,
        /** @var list<BodaccEvent> */
        public array $bodaccEvents = [],
        public bool $bodaccConsulted = false,
        public bool $financesAvailable = false,
    ) {}

    public function ageEntrepriseAnnees(?CarbonImmutable $reference = null): ?int
    {
        if ($this->dateCreation === null) {
            return null;
        }
        $ref = $reference ?? CarbonImmutable::now();

        return (int) $this->dateCreation->diffInYears($ref);
    }

    public function ageDirigeantAnnees(?CarbonImmutable $reference = null): ?int
    {
        if ($this->dateNaissanceDirigeant === null) {
            return null;
        }
        $ref = $reference ?? CarbonImmutable::now();

        return (int) $this->dateNaissanceDirigeant->diffInYears($ref);
    }

    public function variationCa(): ?float
    {
        if ($this->chiffreAffairesN === null || $this->chiffreAffairesNm1 === null || $this->chiffreAffairesNm1 === 0) {
            return null;
        }

        return ($this->chiffreAffairesN - $this->chiffreAffairesNm1) / $this->chiffreAffairesNm1;
    }

    public function hasProcedureCollective(): bool
    {
        foreach ($this->bodaccEvents as $event) {
            if ($event->type === BodaccEventType::ProcedureCollective) {
                return true;
            }
        }

        return false;
    }

    public function emailDomain(): ?string
    {
        if (empty($this->emailContact) || ! str_contains($this->emailContact, '@')) {
            return null;
        }

        return strtolower(trim(substr(strrchr($this->emailContact, '@') ?: '', 1)));
    }

    /**
     * Copie immuable enrichie avec les finances (post-INPI).
     */
    public function withFinances(?int $chiffreAffairesN, ?int $chiffreAffairesNm1, ?int $resultatNet): self
    {
        return new self(
            siren: $this->siren,
            nomEntreprise: $this->nomEntreprise,
            codeNaf: $this->codeNaf,
            natureJuridique: $this->natureJuridique,
            trancheEffectif: $this->trancheEffectif,
            dateCreation: $this->dateCreation,
            dateNaissanceDirigeant: $this->dateNaissanceDirigeant,
            dateNominationDirigeant: $this->dateNominationDirigeant,
            siteInternet: $this->siteInternet,
            emailContact: $this->emailContact,
            chiffreAffairesN: $chiffreAffairesN,
            chiffreAffairesNm1: $chiffreAffairesNm1,
            resultatNet: $resultatNet,
            nombreEtablissements: $this->nombreEtablissements,
            latitude: $this->latitude,
            longitude: $this->longitude,
            distanceKmHome: $this->distanceKmHome,
            bodaccEvents: $this->bodaccEvents,
            bodaccConsulted: $this->bodaccConsulted,
            financesAvailable: $chiffreAffairesN !== null,
        );
    }

    public function siteDomain(): ?string
    {
        if (empty($this->siteInternet)) {
            return null;
        }

        $host = parse_url($this->siteInternet, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            // Pas d'URL complète : on tente le strip de slashs.
            $host = preg_replace('#^https?://#i', '', $this->siteInternet) ?? '';
            $host = strtok($host, '/') ?: $host;
        }

        $host = preg_replace('/^www\./i', '', strtolower($host));

        return $host !== '' ? $host : null;
    }
}
