<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportStationsRequest extends FormRequest
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
            'stations' => 'required|array',
            'stations.*.county' => 'required|string',
            'stations.*.constituency' => 'required|string',
            'stations.*.office' => 'required|string',
            'stations.*.lat' => 'required|numeric',
            'stations.*.lon' => 'required|numeric',
        ];
    }
}
