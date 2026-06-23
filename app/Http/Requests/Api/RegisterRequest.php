<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'username'       => 'required|string|max:255|unique:users,username',
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:users,email',
            'phone'          => 'nullable|string|max:20',
            'gender'         => 'nullable|in:male,female,other',
            'year_of_birth'  => 'nullable|integer|min:1900|max:' . date('Y'),
            'county'         => 'nullable|string|max:100',
            'constituency'   => 'nullable|string|max:100',
            'ward'           => 'nullable|string|max:100',
            'polling_station'=> 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:100',
            'password'       => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ];
    }
}
