<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBlocRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:blocs',
            'type' => 'required|string|in:economic,political,ethnic',
            'description' => 'nullable|string',
            'county_ids' => 'nullable|array',
            'county_ids.*' => 'integer|exists:counties,id',
            'tribes' => 'nullable|string',
            'tribe_population' => 'nullable|integer|min:0',
            'voting_patterns' => 'nullable|json',
        ];
    }
}