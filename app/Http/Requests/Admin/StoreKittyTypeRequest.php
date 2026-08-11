<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreKittyTypeRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->canAccess('tokens.create') ?? false; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:kitty_types,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
