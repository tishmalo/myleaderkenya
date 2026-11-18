<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAspirantPollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required'],
            'status' => ['nullable', 'in:draft,published'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (count($this->optionLines()) < 2) {
                $validator->errors()->add('options', 'Add at least two poll options.');
            }
        });
    }

    /**
     * Accepts either a newline separated string (web form) or a list (API).
     *
     * @return array<int, string>
     */
    public function optionLines(): array
    {
        $options = $this->input('options');

        $lines = is_array($options)
            ? $options
            : preg_split('/\r\n|\r|\n/', (string) $options);

        return collect($lines)
            ->map(fn ($option): string => trim((string) $option))
            ->filter()
            ->values()
            ->all();
    }

    public function status(): string
    {
        return (string) ($this->input('status') ?? 'draft');
    }
}
