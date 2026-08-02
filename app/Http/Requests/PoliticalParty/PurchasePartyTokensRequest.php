<?php

namespace App\Http\Requests\PoliticalParty;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePartyTokensRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['package_id' => ['required', 'exists:candidate_token_packages,id'], 'phone' => ['required', 'string'], 'email' => ['required', 'email']];
    }
}
