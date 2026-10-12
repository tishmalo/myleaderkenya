<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:60'],
            'name' => ['nullable', 'string', 'max:60', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:255'],
            'grants_admin_access' => ['nullable', 'boolean'],
            'has_full_access' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
