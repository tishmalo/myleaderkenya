<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StopAspirantImpersonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('impersonator_admin_id');
    }

    public function rules(): array
    {
        return [];
    }
}
