<?php

namespace App\Http\Requests\Web;

use App\Models\Position;
use App\Models\User;
use App\Support\PiiProtection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AspirantRegisterRequest extends FormRequest
{
    protected $dontFlash = [
        'aspirant_email', 'aspirant_phone', 'account_email', 'account_phone',
        'password', 'password_confirmation',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $existingCandidate = $this->filled('candidate_id');
        $representative = $this->input('submission_mode') === 'representative';

        return [
            'candidate_id' => ['nullable', 'integer', 'exists:candidates,id'],
            'submission_mode' => ['required', Rule::in(['self', 'representative'])],
            'relationship' => [Rule::requiredIf($representative), 'nullable', Rule::in(['PA', 'campaign_manager'])],

            'aspirant_name' => [Rule::requiredIf(! $existingCandidate), 'nullable', 'string', 'max:255'],
            'nick_name' => ['nullable', 'string', 'max:100'],
            'aspirant_email' => [
                Rule::requiredIf(! $existingCandidate && ! $representative),
                'nullable', 'string', 'lowercase', 'email', 'max:255',
                $this->uniqueAccountEmailRule(! $existingCandidate && ! $representative),
            ],
            'aspirant_phone' => ['nullable', 'string', 'max:20'],

            'account_name' => [Rule::requiredIf($representative), 'nullable', 'string', 'max:255'],
            'account_email' => [
                Rule::requiredIf($existingCandidate || $representative),
                'nullable', 'string', 'lowercase', 'email', 'max:255',
            ],
            'account_phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            'position_id' => [Rule::requiredIf(! $existingCandidate), 'nullable', 'exists:positions,id'],
            'political_party_id' => ['nullable', 'exists:political_parties,id'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'about' => ['nullable', 'string'],
            'county' => ['nullable', 'string', 'max:255'],
            'constituency' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->filled('candidate_id')) {
                return;
            }

            $position = $this->positionName();
            if ($position === '') return;

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

    private function uniqueAccountEmailRule(bool $required): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($required): void {
            if (! $required || blank($value)) return;

            if (User::where('email_hash', PiiProtection::emailBlindIndex((string) $value))->exists()) {
                $fail('An account already exists for that email. Select the existing aspirant or use the claim flow.');
            }
        };
    }

    private function positionName(): string
    {
        return Str::lower((string) (Position::find($this->input('position_id'))?->name ?? ''));
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