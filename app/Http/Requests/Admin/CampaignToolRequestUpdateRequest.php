<?php

namespace App\Http\Requests\Admin;

use App\Models\CampaignToolRequest;
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
            'status' => ['required', 'in:'.implode(',', CampaignToolRequest::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}