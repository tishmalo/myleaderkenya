<?php

namespace App\Http\Requests\PoliticalParty;

use Illuminate\Foundation\Http\FormRequest;

class SearchPartyCandidatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['context' => $this->route('context')]);
    }

    public function rules(): array
    {
        return [
            'context' => ['required', 'in:distribution,claim'],
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ];
    }
}
