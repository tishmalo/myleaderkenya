<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PoliticalPartyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $politicalParty = $this->route('politicalParty') ?? $this->route('political_party');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('political_parties', 'name')->ignore($politicalParty?->id)],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('political_parties', 'slug')->ignore($politicalParty?->id)],
            'abbreviation' => ['nullable', 'string', 'max:40'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'brand_color' => ['nullable', 'string', 'max:20'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'phone_1' => ['nullable', 'string', 'max:50'],
            'phone_2' => ['nullable', 'string', 'max:50'],
            'email_1' => ['nullable', 'email', 'max:255'],
            'email_2' => ['nullable', 'email', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'status' => ['required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
