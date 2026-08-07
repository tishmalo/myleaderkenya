<?php

namespace App\Http\Requests\Web;

use App\Contracts\Repositories\Web\UserProfileRepositoryInterface;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserProfileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'username' => trim((string) $this->input('username')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')),
            'id_number' => trim((string) $this->input('id_number')),
            'country_of_residence' => trim((string) $this->input('country_of_residence')),
            'is_voter' => $this->boolean('is_voter'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = (int) $this->user()->id;
        $profiles = app(UserProfileRepositoryInterface::class);

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($profiles, $userId): void {
                    if ($profiles->usernameExists((string) $value, $userId)) {
                        $fail('The username has already been taken.');
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($profiles, $userId): void {
                    if ($profiles->emailExists((string) $value, $userId)) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{7,20}$/'],
            'id_number' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, Closure $fail) use ($userId): void {
                    if (User::idNumberExists((string) $value, $userId)) {
                        $fail('The ID number has already been taken.');
                    }
                },
            ],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'year_of_birth' => ['required', 'integer', 'min:1900', 'max:' . date('Y')],
            'county' => ['required', 'string', 'max:100', 'exists:counties,name'],
            'constituency' => ['required', 'string', 'max:100', 'exists:constituencies,name'],
            'ward' => ['required', 'string', 'max:100', 'exists:wards,name'],
            'polling_station' => ['required', 'string', 'max:255', 'exists:polling_stations,office'],
            'country_of_residence' => ['required', 'string', 'max:100'],
            'is_voter' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $profiles = app(UserProfileRepositoryInterface::class);

                if (! $profiles->constituencies((string) $this->string('county'))->contains((string) $this->string('constituency'))) {
                    $validator->errors()->add('constituency', 'Select a constituency within the selected county.');
                }

                if (! $profiles->wards((string) $this->string('constituency'))->contains((string) $this->string('ward'))) {
                    $validator->errors()->add('ward', 'Select a ward within the selected constituency.');
                }

                if (! $profiles->pollingStations((string) $this->string('ward'))->contains((string) $this->string('polling_station'))) {
                    $validator->errors()->add('polling_station', 'Select a polling station within the selected ward.');
                }
            },
        ];
    }
}