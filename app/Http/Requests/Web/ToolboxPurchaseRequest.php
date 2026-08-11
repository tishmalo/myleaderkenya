<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToolboxPurchaseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'candidate_token_package_id' => ['required', 'integer', 'exists:candidate_token_packages,id'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'objective' => ['required', Rule::in(['my_kitty', 'support_aspirant'])],
            'kitty_type' => ['nullable', Rule::requiredIf(fn () => $this->input('objective') === 'my_kitty'), Rule::in(['sacco_boost', 'saving', 'business_boost', 'self_help_group', 'chama_boost', 'other'])],
            'candidate_id' => ['nullable', Rule::requiredIf(fn () => $this->input('objective') === 'support_aspirant'), 'integer', 'exists:candidates,id'],
            'message' => ['nullable', Rule::requiredIf(fn () => $this->input('objective') === 'support_aspirant'), 'string', 'max:1000'],
        ];
    }
}
