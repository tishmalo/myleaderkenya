<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePublicPulseSourceAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_key' => ['nullable', 'string', 'max:80'],
            'provider' => ['required', 'string', 'max:80', Rule::in(['x_twscrape', 'x_nitter', 'x_snscrape'])],
            'label' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:120'],
            'encrypted_session_payload' => ['required', 'string', 'min:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();
        $data['source_key'] ??= 'x';

        return $data;
    }
}
