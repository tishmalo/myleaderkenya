<?php

namespace App\Http\Requests\Admin;

use App\Models\CandidateTokenRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateTokenRateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'action_key' => ['required', 'string', 'max:120', 'unique:candidate_token_rates,action_key'],
            'label' => ['required', 'string', 'max:255'],
            'calculation_type' => ['required', Rule::in(CandidateTokenRate::CALCULATION_TYPES)],
            'token_amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
