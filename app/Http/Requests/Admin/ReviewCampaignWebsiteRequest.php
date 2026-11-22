<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewCampaignWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['new', 'in_progress', 'completed', 'cancelled'])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
