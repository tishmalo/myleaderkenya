<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportConstituencyRequest extends FormRequest
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
            'constituencies' => 'required|array',
            'constituencies.*.name' => 'required|string|max:255',
            'constituencies.*.county_id' => 'required|exists:counties,id',
            'constituencies.*.population' => 'nullable|integer',
            'constituencies.*.number_of_seats' => 'nullable|integer',
            'constituencies.*.registered_voters' => 'nullable|integer',
            'constituencies.*.position_name' => 'nullable|string',
        ];
    }
}
