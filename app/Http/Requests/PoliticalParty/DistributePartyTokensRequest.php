<?php

namespace App\Http\Requests\PoliticalParty;

use Illuminate\Foundation\Http\FormRequest;

class DistributePartyTokensRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['candidate_id' => ['required', 'exists:candidates,id'], 'amount' => ['required', 'integer', 'min:1']];
    }
}
