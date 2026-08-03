<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendBulkSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:3', 'max:918'],
            'recipient_source' => ['required', Rule::in(['registered_voters', 'uploaded_contacts'])],
            'support_group_type_id' => [
                Rule::requiredIf($this->input('recipient_source') === 'uploaded_contacts'),
                'nullable',
                Rule::exists('support_group_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'privacy_acknowledged' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'privacy_acknowledged.accepted' => 'You must accept responsibility for the lawful use of these contacts before sending.',
        ];
    }
}