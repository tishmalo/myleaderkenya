<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ignore the current category's own name on the unique check
        $categoryId = $this->route('category')?->id;

        return [
            'name'        => "required|string|max:255|unique:categories,name,{$categoryId}",
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:7',
        ];
    }
}
