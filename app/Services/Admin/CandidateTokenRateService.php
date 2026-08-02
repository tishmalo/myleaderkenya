<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenRateRepositoryInterface;
use App\Models\CandidateTokenRate;
use Illuminate\Support\Str;

class CandidateTokenRateService
{
    public function __construct(private CandidateTokenRateRepositoryInterface $rates) {}

    public function paginate(array $filters = [])
    {
        return $this->rates->paginate($filters);
    }

    public function create(array $data): CandidateTokenRate
    {
        return $this->rates->create($this->normalize($data));
    }

    public function update(CandidateTokenRate $rate, array $data): bool
    {
        return $this->rates->update($rate, $this->normalize($data));
    }

    public function delete(CandidateTokenRate $rate): bool
    {
        return $this->rates->delete($rate);
    }

    private function normalize(array $data): array
    {
        $data['action_key'] = Str::slug($data['action_key']);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
