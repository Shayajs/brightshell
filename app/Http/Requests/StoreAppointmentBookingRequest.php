<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function isHoneypotTriggered(): bool
    {
        return filled($this->input('website'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'appointment_slot_id' => ['required', 'integer', 'exists:appointment_slots,id'],
            'website' => ['nullable', 'string', 'max:0'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'appointment_slot_id.required' => 'Choisissez un créneau disponible.',
            'appointment_slot_id.exists' => 'Ce créneau n’existe plus.',
            'first_name.required' => 'Votre prénom est requis.',
            'last_name.required' => 'Votre nom est requis.',
            'email.required' => 'Votre adresse e-mail est requise.',
            'email.email' => 'L’adresse e-mail saisie n’est pas valide.',
            'message.max' => 'Votre message dépasse la longueur maximale autorisée.',
        ];
    }
}
