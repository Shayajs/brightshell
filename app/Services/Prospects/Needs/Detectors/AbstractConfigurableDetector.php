<?php

declare(strict_types=1);

namespace App\Services\Prospects\Needs\Detectors;

use App\Services\Prospects\Needs\Detector;
use App\Services\Prospects\Needs\NeedSignal;
use App\Services\Prospects\Scoring\Dto\ProspectInput;

/**
 * Base mutualisée des détecteurs lisant leur configuration dans `config/prospects.needs.{key}`.
 *
 * Sous-classe : déclare la clé `key()` + implémente `passes()`. Le reste
 * (label, points, targets, désactivation par config `points=0`) est géré ici.
 */
abstract class AbstractConfigurableDetector implements Detector
{
    /** Clé sous `config/prospects.needs.{key}`. */
    abstract protected function key(): string;

    /**
     * Le détecteur s'applique-t-il à ce prospect ?
     *
     * Retourner :
     *  - true → besoin détecté
     *  - false → besoin absent
     *  - null → données insuffisantes (le détecteur s'abstient, pas de signal)
     */
    abstract protected function passes(ProspectInput $in): ?bool;

    /**
     * Surchargeable : justification métier (1 phrase, FR).
     */
    protected function why(ProspectInput $in): string
    {
        return (string) $this->config('label', '');
    }

    /**
     * Surchargeable : confiance par défaut.
     */
    protected function confidence(ProspectInput $in): int
    {
        return 80;
    }

    /**
     * Surchargeable : contexte additionnel (visible dans le slide-over).
     *
     * @return array<string, mixed>
     */
    protected function context(ProspectInput $in): array
    {
        return [];
    }

    public function detect(ProspectInput $in): ?NeedSignal
    {
        $points = (int) $this->config('points', 0);
        if ($points <= 0) {
            return null;
        }
        $result = $this->passes($in);
        if ($result !== true) {
            return null;
        }

        return new NeedSignal(
            key: $this->key(),
            label: (string) $this->config('label', $this->key()),
            points: $points,
            targets: array_values((array) $this->config('targets', ['global'])),
            confidence: $this->confidence($in),
            why: $this->why($in),
            context: $this->context($in),
        );
    }

    protected function config(string $path, mixed $default = null): mixed
    {
        return config('prospects.needs.'.$this->key().'.'.$path, $default);
    }
}
