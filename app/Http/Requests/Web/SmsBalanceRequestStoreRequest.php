<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class SmsBalanceRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'requested_amount' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
