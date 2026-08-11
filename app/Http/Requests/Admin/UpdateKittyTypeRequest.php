<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKittyTypeRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->canAccess('tokens.update') ?? false; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('kitty_types', 'name')->ignore($this->route('kittyType'))],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
