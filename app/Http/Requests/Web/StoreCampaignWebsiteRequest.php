<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'candidate_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'preferred_domain' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9 ._-]+(\.[a-zA-Z]{2,})?$/'],
            'website_type' => ['required', 'in:standard,premium,custom'],
            'reference_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
