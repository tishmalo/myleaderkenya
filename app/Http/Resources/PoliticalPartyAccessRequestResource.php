<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PoliticalPartyAccessRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'party_title' => $this->party_title,
            'party' => [
                'id' => $this->politicalParty->id,
                'name' => $this->politicalParty->name,
                'slug' => $this->politicalParty->slug,
                'abbreviation' => $this->politicalParty->abbreviation,
            ],
            'account' => [
                'name' => $this->user->name,
                'username' => $this->user->username,
            ],
            'review_notes' => $this->review_notes,
            'submitted_at' => $this->created_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
        ];
    }
}
