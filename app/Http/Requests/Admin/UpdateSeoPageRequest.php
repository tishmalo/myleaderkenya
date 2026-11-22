<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // SeoService trims descriptions at 160 characters.
            'description' => ['required', 'string', 'max:160'],
            'canonical' => ['nullable', 'url', 'max:255'],
        ];
    }
}
