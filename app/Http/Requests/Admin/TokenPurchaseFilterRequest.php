<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TokenPurchaseFilterRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'tab' => ['nullable', Rule::in(['candidate', 'kitty', 'donations'])],
            'search' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'kitty_page' => ['nullable', 'integer', 'min:1'],
            'donation_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
