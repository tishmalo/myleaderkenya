<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'voter_user_id' => ['required', 'integer'],
            'outcome' => ['required', 'in:reached,no_answer,busy,wrong_number,callback,not_interested,supporter,volunteer'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'callback_at' => ['nullable', 'date'],
        ];
    }
}
