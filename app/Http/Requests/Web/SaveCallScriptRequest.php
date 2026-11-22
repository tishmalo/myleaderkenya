<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class SaveCallScriptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'script' => ['required', 'string', 'min:20', 'max:5000'],
            'callback_priority' => ['required', 'in:undecided,supporters,volunteers'],
        ];
    }
}
