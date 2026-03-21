<?php

namespace App\Http\Requests\Admin;

use App\Enums\LegalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'legal_name' => ['nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'legal_status' => ['required', 'string', Rule::in(LegalStatus::values())],
            'vat_registered' => ['sometimes', 'boolean'],
            'vat_number' => ['nullable', 'string', 'max:32'],
            'siret' => ['nullable', 'string', 'max:14'],
            'ape_code' => ['nullable', 'string', 'max:8'],
            'street_line1' => ['nullable', 'string', 'max:255'],
            'street_line2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'public_phone' => ['nullable', 'string', 'max:32'],
            'website_url' => ['nullable', 'string', 'max:512'],
            'activity_description' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
            'publish_street_on_api' => ['sometimes', 'boolean'],
            'publish_siret_on_api' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vat_registered' => $this->boolean('vat_registered'),
            'publish_street_on_api' => $this->boolean('publish_street_on_api'),
            'publish_siret_on_api' => $this->boolean('publish_siret_on_api'),
        ]);
    }
}
