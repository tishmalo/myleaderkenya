<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportBlocRequest extends FormRequest
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
            'blocs' => 'required|array',
            'blocs.*.name' => 'required|string|max:255',
            'blocs.*.type' => 'nullable|string|in:economic,political,ethnic',
            'blocs.*.description' => 'nullable|string',
            'blocs.*.county_ids' => 'nullable|array',
            'blocs.*.county_ids.*' => 'integer|exists:counties,id',
            'blocs.*.tribes' => 'nullable|array',
            'blocs.*.tribe_population' => 'nullable|integer',
            'blocs.*.voting_patterns' => 'nullable|array',
        ];
    }
}