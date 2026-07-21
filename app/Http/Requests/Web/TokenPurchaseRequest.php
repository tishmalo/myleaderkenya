<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class TokenPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'candidate_token_package_id' => ['required', 'integer', 'exists:candidate_token_packages,id'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'payment_reference' => ['required', 'string', 'max:255'],
        ];
    }
}
