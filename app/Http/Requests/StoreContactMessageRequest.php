<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
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
        $type = $this->resolvedType();

        $rules = [
            'type' => ['required', 'string', Rule::in(array_keys(ContactMessage::typeChoices()))],
            'website' => ['nullable', 'string', 'max:0'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:200'],
            'reference' => ['nullable', 'string', 'max:100'],
            'project_title' => ['nullable', 'string', 'max:200'],
            'project_kind' => ['nullable', 'string', Rule::in(array_keys(ContactMessage::projectKindChoices()))],
            'budget_range' => ['nullable', 'string', Rule::in(array_keys(ContactMessage::budgetChoices()))],
            'deadline' => ['nullable', 'string', Rule::in(array_keys(ContactMessage::deadlineChoices()))],
            'body' => ['required', 'string', 'min:10'],
        ];

        switch ($type) {
            case ContactMessage::TYPE_GENERAL:
                $rules['body'][] = 'max:1500';
                break;

            case ContactMessage::TYPE_PROFESSIONAL:
                $rules['subject'] = ['required', 'string', 'max:200'];
                $rules['body'][] = 'max:4000';
                break;

            case ContactMessage::TYPE_COMPLAINT:
                $rules['subject'] = ['required', 'string', 'max:200'];
                $rules['body'][] = 'max:4000';
                break;

            case ContactMessage::TYPE_PROJECT:
                $rules['project_title'] = ['required', 'string', 'max:200'];
                $rules['project_kind'] = ['required', 'string', Rule::in(array_keys(ContactMessage::projectKindChoices()))];
                $rules['budget_range'] = ['required', 'string', Rule::in(array_keys(ContactMessage::budgetChoices()))];
                $rules['deadline'] = ['required', 'string', Rule::in(array_keys(ContactMessage::deadlineChoices()))];
                $rules['body'][] = 'max:20000';
                $rules['attachments'] = ['nullable', 'array', 'max:10'];
                $rules['attachments.*'] = [
                    'file',
                    'max:25600',
                    'mimes:pdf,png,jpg,jpeg,webp,gif,zip,doc,docx,xls,xlsx,ppt,pptx,txt,md,csv,svg',
                ];
                break;
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Votre prénom est requis.',
            'last_name.required' => 'Votre nom est requis.',
            'email.required' => 'Votre adresse e-mail est requise.',
            'email.email' => 'L’adresse e-mail saisie n’est pas valide.',
            'body.required' => 'Le message est requis.',
            'body.min' => 'Votre message doit contenir au moins 10 caractères.',
            'body.max' => 'Votre message dépasse la longueur maximale autorisée.',
            'subject.required' => 'Le sujet est requis pour ce type de demande.',
            'project_title.required' => 'Donnez un titre à votre projet.',
            'project_kind.required' => 'Précisez le type de projet.',
            'budget_range.required' => 'Indiquez une fourchette de budget.',
            'deadline.required' => 'Indiquez un délai souhaité.',
            'attachments.max' => 'Vous ne pouvez joindre que 10 fichiers maximum.',
            'attachments.*.file' => 'Pièce jointe invalide.',
            'attachments.*.max' => 'Chaque pièce jointe doit faire 25 Mo maximum.',
            'attachments.*.mimes' => 'Format de pièce jointe non autorisé.',
        ];
    }

    public function resolvedType(): string
    {
        $type = (string) $this->input('type', ContactMessage::TYPE_GENERAL);

        return array_key_exists($type, ContactMessage::typeChoices())
            ? $type
            : ContactMessage::TYPE_GENERAL;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->resolvedType(),
        ]);
    }
}
