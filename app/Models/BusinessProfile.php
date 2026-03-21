<?php

namespace App\Models;

use App\Enums\LegalStatus;
use Illuminate\Database\Eloquent\Model;

/** Profil « mon entreprise » (ligne unique) — distinct des fiches clients {@see Company}. */
class BusinessProfile extends Model
{
    protected $fillable = [
        'legal_name',
        'trade_name',
        'legal_status',
        'vat_registered',
        'vat_number',
        'siret',
        'ape_code',
        'street_line1',
        'street_line2',
        'postal_code',
        'city',
        'country',
        'public_email',
        'public_phone',
        'website_url',
        'activity_description',
        'internal_notes',
        'publish_street_on_api',
        'publish_siret_on_api',
    ];

    protected function casts(): array
    {
        return [
            'vat_registered' => 'boolean',
            'publish_street_on_api' => 'boolean',
            'publish_siret_on_api' => 'boolean',
        ];
    }

    /** L’unique fiche entreprise (créée par la migration). */
    public static function singleton(): self
    {
        $row = static::query()->first();
        if ($row !== null) {
            return $row;
        }

        return static::query()->create([
            'legal_status' => LegalStatus::AutoEntrepreneur->value,
            'country' => 'France',
        ]);
    }

    public function legalStatusEnum(): LegalStatus
    {
        return LegalStatus::tryFromString($this->legal_status)
            ?? LegalStatus::AutoEntrepreneur;
    }

    public function displayName(): string
    {
        $trade = trim((string) $this->trade_name);
        if ($trade !== '') {
            return $trade;
        }

        $legal = trim((string) $this->legal_name);

        return $legal !== '' ? $legal : 'Entreprise';
    }
}
