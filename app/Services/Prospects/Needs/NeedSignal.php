<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs;

/**
 * Signal de besoin détecté pour un prospect.
 *
 * Plusieurs signaux peuvent émerger pour le même prospect, chacun apportant
 * ses propres points sur une ou plusieurs cibles commerciales (website / software / global).
 *
 * Persisté dans `score_breakdown.needs[]` pour l'explicabilité (slide-over UI).
 */
final readonly class NeedSignal
{
    /**
     * @param  list<string>  $targets  ex. ['website'] ou ['software', 'global'] ou ['*']
     */
    public function __construct(
        public string $key,        // ex. 'website_absent'
        public string $label,      // ex. 'Aucun site web déclaré'
        public int $points,        // ex. 20
        public array $targets,
        public int $confidence,    // 0..100 — qualité de la détection
        public string $why,        // raisonnement métier (1 phrase FR)
        /** @var array<string, mixed> */
        public array $context = [],
    ) {}

    public function appliesTo(string $target): bool
    {
        return in_array('*', $this->targets, true) || in_array($target, $this->targets, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'points' => $this->points,
            'targets' => $this->targets,
            'confidence' => $this->confidence,
            'why' => $this->why,
            'context' => $this->context,
        ];
    }
}
