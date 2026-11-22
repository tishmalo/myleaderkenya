<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LinkParliamentMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_id' => ['nullable', 'integer', 'exists:candidates,id'],
        ];
    }

    public function candidateId(): ?int
    {
        $candidateId = $this->validated()['candidate_id'] ?? null;

        return $candidateId === null ? null : (int) $candidateId;
    }
}
