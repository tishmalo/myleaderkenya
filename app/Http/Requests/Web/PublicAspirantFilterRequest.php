<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class PublicAspirantFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate' => ['nullable', 'string', 'max:120'],
            'search' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:50'],
            'political_party' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'county' => ['nullable', 'string', 'max:100'],
            'constituency' => ['nullable', 'string', 'max:120'],
            'ward' => ['nullable', 'string', 'max:120'],
            'bloc' => ['nullable', 'integer', 'exists:blocs,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $values = [];

        foreach (['candidate', 'search', 'position', 'political_party', 'country', 'county', 'constituency', 'ward'] as $key) {
            if (! $this->has($key) || ! is_string($this->input($key))) {
                continue;
            }

            $value = trim($this->input($key));
            $values[$key] = $value === '' ? null : $value;
        }

        $this->merge($values);
    }
}
