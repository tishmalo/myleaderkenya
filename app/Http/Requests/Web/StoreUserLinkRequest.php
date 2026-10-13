<?php

namespace App\Http\Requests\Web;

use App\Models\Candidate;
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
            // National links (presidential aspirants, country wide pages and
            // groups) have no county; everything else still needs one. A
            // presidential aspirant makes the link national on its own.
            'is_national' => ['nullable', 'boolean'],
            'county_id' => [
                'nullable',
                'integer',
                'exists:counties,id',
                Rule::requiredIf(fn (): bool => ! $this->targetsTheWholeCountry()),
            ],
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
            'county_id.required' => 'Select a county, or tick "National link" for a presidential aspirant or a country wide page.',
            'constituency_id.exists' => 'Select a valid constituency.',
            'ward_id.exists' => 'Select a valid ward.',
            'political_party_id.exists' => 'Select a valid political party.',
            'candidate_id.exists' => 'Select an approved aspirant.',
        ];
    }

    /**
     * Whether this submission is a national link: the clerk ticked the box, or
     * the chosen aspirant is a presidential candidate who has no county.
     */
    private function targetsTheWholeCountry(): bool
    {
        if (filter_var($this->input('is_national'), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $candidateId = $this->input('candidate_id');

        if (! $candidateId) {
            return false;
        }

        return (bool) Candidate::query()
            ->with('position:id,name')
            ->find((int) $candidateId)
            ?->isPresidential();
    }
}
