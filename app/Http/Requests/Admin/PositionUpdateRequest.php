<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PositionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $positionId = $this->route('position') ? $this->route('position')->id : '';

        return [
            'name'        => 'required|string|max:255|unique:positions,name,' . $positionId,
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'required|integer|min:0',
        ];
    }
}

