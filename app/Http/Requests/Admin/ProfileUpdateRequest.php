<?php

namespace App\Http\Requests\Admin;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (app(UserRepositoryInterface::class)->existsByEmailHash((string) $value, $this->user()->id)) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
        ];
    }
}
