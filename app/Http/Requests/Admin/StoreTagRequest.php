<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:tags,name',
            'slug'        => 'required|string|max:100|unique:tags,slug|regex:/^[a-z0-9-]+$/',
            'description' => 'nullable|string|max:500',
        ];
    }
}
