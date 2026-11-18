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
            'options' => ['required', 'string'],
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
     * @return array<int, string>
     */
    public function optionLines(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->input('options')))
            ->map(fn (string $option): string => trim($option))
            ->filter()
            ->values()
            ->all();
    }

    public function status(): string
    {
        return (string) ($this->input('status') ?? 'draft');
    }
}
