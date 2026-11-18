<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateCampaignPrioritiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'priorities' => ['nullable', 'array'],
            'priorities.*.manifesto' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public function priorities(): array
    {
        return $this->validated()['priorities'] ?? [];
    }
}
