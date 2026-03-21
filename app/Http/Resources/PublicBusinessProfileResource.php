<?php

namespace App\Http\Resources;

use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Données volontairement limitées (pas de notes internes, pas d’adresse complète sauf opt-in).
 *
 * @mixin BusinessProfile
 */
class PublicBusinessProfileResource extends JsonResource
{
    /** JSON plat (pas de clé « data ») pour intégrations simples. */
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $p = $this->resource;
        $status = $p->legalStatusEnum();

        $location = [
            'city' => $p->city,
            'postal_code' => $p->postal_code,
            'country' => $p->country,
        ];

        if ($p->publish_street_on_api) {
            $location['street_line1'] = $p->street_line1;
            $location['street_line2'] = $p->street_line2;
        }

        $out = [
            'display_name' => $p->displayName(),
            'legal_name' => $p->legal_name,
            'trade_name' => $p->trade_name,
            'legal_status' => $status->value,
            'legal_status_label' => $status->label(),
            'vat' => [
                'registered' => (bool) $p->vat_registered,
                'number' => $p->vat_registered && $p->vat_number ? $p->vat_number : null,
                'note' => $p->vat_registered
                    ? 'Assujetti à la TVA — numéro communiqué si renseigné.'
                    : 'Non assujetti à la TVA sur les prestations (régime actuel).',
            ],
            'activity' => $p->activity_description,
            'ape_code' => $p->ape_code,
            'contact' => array_filter([
                'email' => $p->public_email,
                'phone' => $p->public_phone,
                'website' => $p->website_url,
            ]),
            'location' => array_filter($location, fn ($v) => $v !== null && $v !== ''),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];

        if ($p->publish_siret_on_api && $p->siret) {
            $out['siret'] = $p->siret;
        }

        return $out;
    }
}
