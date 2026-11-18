<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class BotVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'g-recaptcha-response' => ['required', 'string'],
            'intended' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function token(): string
    {
        return (string) $this->validated()['g-recaptcha-response'];
    }

    public function intended(): string
    {
        return (string) ($this->validated()['intended'] ?? '');
    }
}
