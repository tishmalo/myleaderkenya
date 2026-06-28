<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoalitionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $coalition = $this->route('coalition');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('coalitions', 'name')->ignore($coalition?->id)],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('coalitions', 'slug')->ignore($coalition?->id)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'brand_color' => ['nullable', 'string', 'max:20'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'status' => ['required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'political_parties' => ['nullable', 'array'],
            'political_parties.*' => ['exists:political_parties,id'],
        ];
    }
}
