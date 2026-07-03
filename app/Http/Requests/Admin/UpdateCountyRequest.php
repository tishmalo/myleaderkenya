<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCountyRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:counties,name,' . $this->route('county')->id,
            'bloc_id' => 'nullable|exists:blocs,id',
            'bloc_ids' => 'nullable|array',
            'bloc_ids.*' => 'integer|exists:blocs,id',
            'area' => 'nullable|string',
            'population' => 'nullable|integer|min:0',
            'capital' => 'nullable|string',
            'registered_voters' => 'nullable|integer|min:0',
            'postal_abbreviation' => 'nullable|string|max:10',
        ];
    }
}