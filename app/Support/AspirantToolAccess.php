<?php

namespace App\Support;

use App\Models\CampaignTool;
use App\Models\Candidate;

final readonly class AspirantToolAccess
{
    /**
     * @param  array<string, mixed>|null  $definition
     * @param  array<string, mixed>  $scope
     */
    public function __construct(
        public ?string $key,
        public ?array $definition,
        public ?Candidate $candidate,
        public ?CampaignTool $tool,
        public array $scope,
        public ?string $denial = null,
    ) {}

    public function allowed(): bool
    {
        return $this->exists() && $this->denialMessage() === null;
    }

    public function exists(): bool
    {
        return $this->definition !== null && $this->key !== null;
    }

    public function denialMessage(): ?string
    {
        if ($this->denial !== null) {
            return $this->denial;
        }

        return $this->candidate === null
            ? 'No aspirant profile is linked to this account yet.'
            : null;
    }

    public function voterFacing(): bool
    {
        return (bool) ($this->definition['voter_facing'] ?? false);
    }

    public function scopeMissing(): bool
    {
        return (bool) ($this->scope['missing'] ?? true);
    }

    public function scopeLabel(): string
    {
        return (string) ($this->scope['label'] ?? '');
    }

    public function scopeMessage(): ?string
    {
        return $this->scope['message'] ?? null;
    }
}
