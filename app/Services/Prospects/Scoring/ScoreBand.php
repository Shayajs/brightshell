<?php

declare(strict_types=1);

namespace App\Services\Prospects\Scoring;

/**
 * Bande de score finale d'un prospect.
 *
 * - Hot       : ≥ 120 — appel immédiat (badge rouge animé)
 * - Priority  : 80..119
 * - Standard  : 50..79
 * - Watch     : 25..49 — veille passive
 * - Excluded  : < 25 ou veto absolu (procédure collective)
 */
enum ScoreBand: string
{
    case Hot = 'hot';
    case Priority = 'priority';
    case Standard = 'standard';
    case Watch = 'watch';
    case Excluded = 'excluded';

    /**
     * Libellé FR pour l'UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Hot => 'Hot · appel immédiat',
            self::Priority => 'Prioritaire',
            self::Standard => 'Standard',
            self::Watch => 'Veille',
            self::Excluded => 'Exclu',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Hot => 'Hot',
            self::Priority => 'Prioritaire',
            self::Standard => 'Standard',
            self::Watch => 'Veille',
            self::Excluded => 'Exclu',
        };
    }

    /**
     * Classes Tailwind du badge (fond + texte + bordure).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Hot => 'bg-red-500/10 text-red-400 border border-red-500/20',
            self::Priority => 'bg-orange-500/10 text-orange-400 border border-orange-500/20',
            self::Standard => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
            self::Watch => 'bg-slate-700 text-slate-300 border border-slate-600',
            self::Excluded => 'bg-zinc-800 text-zinc-500 border border-zinc-700',
        };
    }

    /**
     * Couleur d'accent (utilisée pour les KPI ou les barres).
     */
    public function accent(): string
    {
        return match ($this) {
            self::Hot => 'text-red-400',
            self::Priority => 'text-orange-400',
            self::Standard => 'text-emerald-400',
            self::Watch => 'text-slate-400',
            self::Excluded => 'text-zinc-500',
        };
    }

    /**
     * Niveau legacy 0..3 (compat brief initial).
     */
    public function legacyNiveau(): int
    {
        return match ($this) {
            self::Hot, self::Priority => 3,
            self::Standard => 2,
            self::Watch => 1,
            self::Excluded => 0,
        };
    }

    /**
     * Ordre de tri (Hot en haut).
     */
    public function sortOrder(): int
    {
        return match ($this) {
            self::Hot => 0,
            self::Priority => 1,
            self::Standard => 2,
            self::Watch => 3,
            self::Excluded => 4,
        };
    }
}
