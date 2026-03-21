<?php

namespace App\Enums;

enum LegalStatus: string
{
    case AutoEntrepreneur = 'auto_entrepreneur';
    case EI = 'ei';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::AutoEntrepreneur => 'Auto-entrepreneur',
            self::EI => 'Entreprise individuelle (EI)',
            self::Other => 'Autre statut',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }
}
