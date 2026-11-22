<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EmailOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ];
    }

    public function email(): string
    {
        return (string) $this->validated()['email'];
    }

    public function otp(): string
    {
        return (string) $this->validated()['otp'];
    }
}
