<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nick_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (User::emailExists((string) $value)) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'position_id' => ['required', 'exists:positions,id'],
            'political_party_id' => ['nullable', 'exists:political_parties,id'],
            'about' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'county' => ['nullable', 'string', 'max:100'],
            'constituency' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
        ];
    }
}
