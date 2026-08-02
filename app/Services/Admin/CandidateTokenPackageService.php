<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Models\CandidateTokenPackage;

class CandidateTokenPackageService
{
    public function __construct(private CandidateTokenPackageRepositoryInterface $packages) {}

    public function paginate(array $filters = [])
    {
        return $this->packages->paginate($filters);
    }

    public function create(array $data): CandidateTokenPackage
    {
        return $this->packages->create($this->normalize($data));
    }

    public function update(CandidateTokenPackage $package, array $data): bool
    {
        return $this->packages->update($package, $this->normalize($data));
    }

    public function delete(CandidateTokenPackage $package): bool
    {
        return $this->packages->delete($package);
    }

    private function normalize(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['currency'] = strtoupper($data['currency'] ?? 'KES');

        return $data;
    }
}
