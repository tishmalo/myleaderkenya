<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportCountyRequest extends FormRequest
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
            'counties' => 'required|array',
            'counties.*.name' => 'required|string|max:255',
            'counties.*.bloc_id' => 'nullable|exists:blocs,id',
            'counties.*.bloc_ids' => 'nullable|array',
            'counties.*.bloc_ids.*' => 'integer|exists:blocs,id',
            'counties.*.area' => 'nullable|string',
            'counties.*.population' => 'nullable|integer|min:0',
            'counties.*.capital' => 'nullable|string',
            'counties.*.registered_voters' => 'nullable|integer|min:0',
            'counties.*.postal_abbreviation' => 'nullable|string|max:10',
        ];
    }
}