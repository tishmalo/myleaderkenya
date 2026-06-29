<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LiveStatFigureStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'figures' => ['required', 'array'],
            'figures.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
