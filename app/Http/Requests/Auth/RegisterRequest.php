<?php

namespace App\Http\Requests\Auth;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                if (app(UserRepositoryInterface::class)->existsByEmailHash((string) $value)) {
                    $fail('An account already exists for that email.');
                }
            }],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
