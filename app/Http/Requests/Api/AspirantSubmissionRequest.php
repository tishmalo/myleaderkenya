<?php

namespace App\Http\Requests\Api;

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AspirantSubmissionRequest extends FormRequest
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $position = $this->positionName();

            if ($position === '') {
                return;
            }

            if ($this->requiresCounty($position) && blank($this->input('county'))) {
                $validator->errors()->add('county', 'Select the county for this aspirant position.');
            }

            if ($this->requiresConstituency($position) && blank($this->input('constituency'))) {
                $validator->errors()->add('constituency', 'Select the constituency for this aspirant position.');
            }

            if ($this->requiresWard($position) && blank($this->input('ward'))) {
                $validator->errors()->add('ward', 'Select the ward for this aspirant position.');
            }
        });
    }

    private function positionName(): string
    {
        $position = Position::find($this->input('position_id'));

        return Str::lower((string) ($position->name ?? ''));
    }

    private function requiresCounty(string $position): bool
    {
        return ! str_contains($position, 'president');
    }

    private function requiresConstituency(string $position): bool
    {
        return str_contains($position, 'member of parliament')
            || preg_match('/\bmp\b/', $position)
            || str_contains($position, 'mca')
            || str_contains($position, 'member of county assembly')
            || str_contains($position, 'county assembly');
    }

    private function requiresWard(string $position): bool
    {
        return str_contains($position, 'mca')
            || str_contains($position, 'member of county assembly')
            || str_contains($position, 'county assembly');
    }
}