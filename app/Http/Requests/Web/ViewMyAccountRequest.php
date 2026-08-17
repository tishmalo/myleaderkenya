<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewMyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'destination' => ['nullable', 'string', Rule::in(['account'])],
        ];
    }

    public function explicitlyRequestsAccount(): bool
    {
        return $this->validated('destination') === 'account';
    }
}
