<?php

namespace App\Http\Requests\Web;

use App\Models\CandidateClaimRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class StoreCandidateClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relationship' => ['required', Rule::in(CandidateClaimRequest::RELATIONSHIPS)],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $hash = hash('sha256', Str::lower(trim((string) $value)));

                    if (User::where('email_hash', $hash)->exists()) {
                        $fail('An account already exists for that email. Please sign in instead.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
