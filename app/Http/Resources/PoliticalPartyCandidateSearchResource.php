<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PoliticalPartyCandidateSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->nick_name,
            'image_url' => $this->profile_picture
                ? Storage::url($this->profile_picture)
                : null,
            'position' => $this->position?->name,
            'party' => $this->politicalParty?->name,
            'jurisdiction' => collect([
                $this->ward,
                $this->constituency,
                $this->county,
                $this->country,
            ])->filter()->unique()->implode(', '),
        ];
    }
}
