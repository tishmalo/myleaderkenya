<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStationRequest extends FormRequest
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
            'bloc_id' => 'nullable|exists:blocs,id',
            'county' => 'required|string|max:100',
            'constituency' => 'required|string|max:100',
            'ward' => 'required|string|max:100',
            'office' => 'required|string|max:255',
            'near_landmark' => 'nullable|string|max:255',
            'distance_to_office' => 'nullable|integer|min:0',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'registered_voters' => 'nullable|integer|min:0',
        ];
    }
}
