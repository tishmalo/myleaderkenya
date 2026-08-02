<?php

namespace App\Http\Requests\Admin;

use App\Models\CandidateTokenRate;
use Illuminate\Validation\Rule;

class CandidateTokenRateUpdateRequest extends CandidateTokenRateStoreRequest
{
    public function rules(): array
    {
        $rate = $this->route('candidate_token_rate') ?? $this->route('candidateTokenRate');

        return [
            'action_key' => ['required', 'string', 'max:120', Rule::unique('candidate_token_rates', 'action_key')->ignore($rate)],
            'label' => ['required', 'string', 'max:255'],
            'calculation_type' => ['required', Rule::in(CandidateTokenRate::CALCULATION_TYPES)],
            'token_amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}


