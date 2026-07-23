<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CandidateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nick_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'position_id' => 'required|exists:positions,id',
            'political_party_id' => 'nullable|exists:political_parties,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'about' => 'nullable|string',
            'country' => 'nullable|string',
            'county' => 'nullable|string',
            'constituency' => 'nullable|string',
            'ward' => 'nullable|string',
            'support_contacts' => ['nullable', 'array'],
            'support_contacts.*.id' => ['nullable', 'integer'],
            'support_contacts.*.support_group_type_id' => ['nullable', 'exists:support_group_types,id'],
            'support_contacts.*.name' => ['nullable', 'string', 'max:255'],
            'support_contacts.*.email' => ['nullable', 'email', 'max:255'],
            'support_contacts.*.phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'sms_enabled' => 'nullable|boolean',
            'sms_provider' => 'nullable|in:infobip',
            'sms_base_url' => 'nullable|url|max:255',
            'sms_sender_name' => 'nullable|string|max:50',
            'sms_username' => 'nullable|string|max:255',
            'sms_password' => 'nullable|string|max:500',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('support_contacts', []) as $index => $contact) {
                $hasAny = filled($contact['support_group_type_id'] ?? null)
                    || filled($contact['name'] ?? null)
                    || filled($contact['email'] ?? null)
                    || filled($contact['phone'] ?? null);

                if (! $hasAny) {
                    continue;
                }

                if (blank($contact['support_group_type_id'] ?? null)) {
                    $validator->errors()->add("support_contacts.$index.support_group_type_id", 'Choose a support group.');
                }

                if (blank($contact['name'] ?? null)) {
                    $validator->errors()->add("support_contacts.$index.name", 'Enter the support contact name.');
                }

                if (blank($contact['email'] ?? null) && blank($contact['phone'] ?? null)) {
                    $validator->errors()->add("support_contacts.$index.phone", 'Enter an email or phone for each support contact.');
                }
            }
        });
    }
}
