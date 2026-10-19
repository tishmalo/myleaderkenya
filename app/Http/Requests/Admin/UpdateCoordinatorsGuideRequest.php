<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCoordinatorsGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccess('guide.update');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'The guide cannot be saved empty.',
            'body.max' => 'The guide is too long. Keep it under 100,000 characters.',
        ];
    }
}
