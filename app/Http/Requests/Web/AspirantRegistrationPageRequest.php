<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AspirantRegistrationPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_id' => ['nullable', 'integer', 'min:1'],
            'submission_mode' => ['nullable', 'string', Rule::in(['self', 'representative', 'adoption'])],
            'modal' => ['nullable', 'boolean'],
        ];
    }
}
