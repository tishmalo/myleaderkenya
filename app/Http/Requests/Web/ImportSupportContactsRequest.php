<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ImportSupportContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'support_group_type_id' => [
                'nullable',
                Rule::exists('support_group_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'contacts_file' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ];
    }

    public function messages(): array
    {
        return [
            'support_group_type_id.exists' => 'Choose an active default support group.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $file = $this->file('contacts_file');

            if ($file && Str::lower($file->getClientOriginalExtension()) === 'xls') {
                $validator->errors()->add(
                    'contacts_file',
                    'Legacy XLS files are not supported. Save the file as XLSX or CSV, then upload again.'
                );
            }
        });
    }

    public function defaultGroupId(): ?int
    {
        $groupId = $this->input('support_group_type_id');

        return blank($groupId) ? null : (int) $groupId;
    }
}
