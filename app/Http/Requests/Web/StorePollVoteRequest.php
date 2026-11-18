<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StorePollVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_id' => ['required', 'integer'],
        ];
    }

    public function optionId(): int
    {
        return (int) $this->validated()['option_id'];
    }
}
