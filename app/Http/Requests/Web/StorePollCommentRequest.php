<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StorePollCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ];
    }

    public function body(): string
    {
        return (string) $this->validated()['body'];
    }
}
