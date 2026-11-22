<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CandidateFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable'],
            'political_party' => ['nullable'],
            'approval_status' => ['nullable', 'string', 'max:50'],
            'account_claim' => ['nullable', 'string', 'max:50'],
            'import_filter' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * The filters shared by the listing table and the CSV export.
     */
    public function filters(): array
    {
        return $this->validated();
    }
}
