<?php

namespace App\Http\Requests\PoliticalParty;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartyOfficialStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:active,suspended']];
    }
}
