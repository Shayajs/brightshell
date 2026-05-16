<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring\Dto;

/**
 * Acte BODACC normalisé (sortie de BodaccClient).
 *
 * Les libellés bruts du BODACC ne sont pas standardisés ; cette classe encapsule
 * le type métier détecté (`type`) qu'on utilise dans le scoring + le payload brut.
 */
final readonly class BodaccEvent
{
    public function __construct(
        public BodaccEventType $type,
        public \DateTimeImmutable $date,
        public string $libelle,
        /** @var array<string, mixed> */
        public array $payload = [],
    ) {}

    public function ageInMonths(?\DateTimeImmutable $reference = null): float
    {
        $now = $reference ?? new \DateTimeImmutable;
        $diff = $now->diff($this->date);
        // diff y * 12 + m + j/30 (approx)
        return ((int) $diff->y) * 12 + (int) $diff->m + (int) $diff->d / 30.4375;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'date' => $this->date->format('Y-m-d'),
            'libelle' => $this->libelle,
            'payload' => $this->payload,
        ];
    }
}
