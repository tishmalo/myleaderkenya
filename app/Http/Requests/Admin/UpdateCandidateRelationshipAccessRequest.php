<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateRelationshipAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccess('aspirants.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'dashboard_access_enabled' => ['required', 'boolean'],
        ];
    }
}
