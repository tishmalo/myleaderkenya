<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportStationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stations' => 'required|array|min:1',
            'stations.*.county'       => 'required|string|max:100',
            'stations.*.constituency' => 'required|string|max:100',
            'stations.*.office'       => 'required|string|max:255',
            'stations.*.near_landmark'=> 'nullable|string|max:255',
            'stations.*.distance_to_office' => 'nullable|integer',
            'stations.*.lat'          => 'required|numeric|between:-90,90',
            'stations.*.lon'          => 'required|numeric|between:-180,180',
        ];
    }
}
