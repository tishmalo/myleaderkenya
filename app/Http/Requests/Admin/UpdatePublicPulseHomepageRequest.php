<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicPulseHomepageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'candidate_ids' => ['nullable', 'array', 'max:5'],
            'candidate_ids.*' => ['integer', 'distinct', 'exists:candidates,id'],
            'orders' => ['nullable', 'array'],
            'orders.*' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
