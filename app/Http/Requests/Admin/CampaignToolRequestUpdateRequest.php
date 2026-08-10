<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CampaignToolRequestUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('campaign-tool-requests.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:start_fulfilment,activate,reject,refund'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
