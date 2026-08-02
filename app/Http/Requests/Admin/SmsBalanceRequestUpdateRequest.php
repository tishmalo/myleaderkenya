<?php

namespace App\Http\Requests\Admin;

use App\Models\CandidateSmsBalanceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmsBalanceRequestUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(CandidateSmsBalanceRequest::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
