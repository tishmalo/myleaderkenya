<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ReplyAspirantSupportRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['reply' => ['required', 'string', 'max:1000']]; }
}
