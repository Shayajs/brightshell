<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs;

use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Container des détecteurs de besoins.
 *
 * Conserve l'ordre de déclaration (priorité visuelle dans le slide-over).
 * Filtre les besoins selon un seuil de confiance global.
 */
final class NeedsDetector
{
    /**
     * @param  list<Detector>  $detectors
     */
    public function __construct(
        private readonly array $detectors,
        private readonly int $minConfidence = 0,
    ) {}

    /**
     * @return list<NeedSignal>
     */
    public function detectAll(ProspectInput $in): array
    {
        $needs = [];
        foreach ($this->detectors as $detector) {
            try {
                $signal = $detector->detect($in);
            } catch (\Throwable) {
                continue;
            }
            if ($signal !== null && $signal->confidence >= $this->minConfidence) {
                $needs[] = $signal;
            }
        }

        return $needs;
    }
}
