<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'string', Rule::in(['mpesa', 'bank_transfer', 'paypal', 'cash', 'other'])],
            'amount' => ['required', 'numeric', 'min:1'],
            'details' => ['nullable', 'string'],
            'payment_details' => ['nullable', 'array'],
            'status' => ['required', Rule::in(['pending', 'completed', 'failed', 'refunded'])],
        ];
    }
}
