<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpamRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['keyword', 'domain', 'email', 'phone', 'ip', 'regex'])],
            'value' => ['required', 'string', 'max:500'],
        ];
    }

    public function type(): string
    {
        return (string) $this->validated()['type'];
    }

    public function value(): string
    {
        return trim((string) $this->validated()['value']);
    }
}
