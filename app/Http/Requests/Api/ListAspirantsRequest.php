<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListAspirantsRequest extends FormRequest
{
    private const FILTERS = [
        'featured',
        'county',
        'constituency',
        'ward',
        'position',
        'political_party',
        'candidate',
        'search',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'featured' => ['nullable'],
            'county' => ['nullable', 'string', 'max:255'],
            'constituency' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'political_party' => ['nullable', 'string', 'max:255'],
            'candidate' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return array_intersect_key($this->validated(), array_flip(self::FILTERS));
    }

    public function perPage(): int
    {
        return (int) ($this->validated()['per_page'] ?? 12);
    }
}
