<?php

namespace App\Http\Requests\Web;

use App\Models\ResourceLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', Rule::in(array_keys(ResourceLink::PLATFORMS))],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'county_id' => ['required', 'integer', 'exists:counties,id'],
            'constituency_id' => ['nullable', 'integer', 'exists:constituencies,id'],
            'ward_id' => ['nullable', 'integer', 'exists:wards,id'],
            'political_party_id' => ['nullable', 'integer', 'exists:political_parties,id'],
            'candidate_id' => ['nullable', 'integer', 'exists:candidates,id'],
            'followers' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'platform.in' => 'Select a valid link platform.',
            'title.required' => 'Give the page or group a title (e.g. Manaichi Linda).',
            'county_id.required' => 'Select the county this link belongs to.',
            'constituency_id.exists' => 'Select a valid constituency.',
            'ward_id.exists' => 'Select a valid ward.',
            'political_party_id.exists' => 'Select a valid political party.',
            'candidate_id.exists' => 'Select an approved aspirant.',
        ];
    }
}
