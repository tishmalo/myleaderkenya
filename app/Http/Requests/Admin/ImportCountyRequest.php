<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportCountyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'counties' => 'required|array',
            'counties.*.name' => 'required|string|max:255',
            'counties.*.bloc_id' => 'required|exists:blocs,id',
            'counties.*.area' => 'nullable|string',
            'counties.*.population' => 'nullable|integer',
            'counties.*.capital' => 'nullable|string',
            'counties.*.registered_voters' => 'nullable|integer',
            'counties.*.postal_abbreviation' => 'nullable|string|max:10',
        ];
    }
}
