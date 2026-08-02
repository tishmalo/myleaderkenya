<?php

namespace App\Http\Requests\Admin;

use App\Models\CandidateClaimRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewCandidateClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('aspirants.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                CandidateClaimRequest::STATUS_APPROVED,
                CandidateClaimRequest::STATUS_REJECTED,
            ])],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
