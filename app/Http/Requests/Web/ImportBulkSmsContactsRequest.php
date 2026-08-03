<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportBulkSmsContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'support_group_type_id' => [
                'required',
                Rule::exists('support_group_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'contacts_file' => ['required', 'file', 'max:5120', 'mimes:xlsx,csv,txt'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'privacy_acknowledged.accepted' => 'You must confirm that you have a lawful basis to process and message these contacts.',
        ];
    }
}