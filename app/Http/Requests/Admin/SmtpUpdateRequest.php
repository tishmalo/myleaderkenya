<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SmtpUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mail_host'         => 'required',
            'mail_port'         => 'required',
            'mail_username'     => 'required',
            'mail_password'     => 'required',
            'mail_encryption'   => 'nullable',
            'mail_from_address' => 'required|email',
            'mail_from_name'    => 'required',
        ];
    }
}
