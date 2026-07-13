<?php

namespace App\Http\Requests\Web;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AspirantRegisterRequest extends FormRequest
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
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $hash = hash('sha256', Str::lower(trim((string) $value)));

                    if (User::where('email_hash', $hash)->exists()) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'position_id' => ['required', 'exists:positions,id'],
            'political_party_id' => ['nullable', 'exists:political_parties,id'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'about' => ['nullable', 'string'],
            'county' => ['nullable', 'string', 'max:255'],
            'constituency' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
        ];
    }
}
