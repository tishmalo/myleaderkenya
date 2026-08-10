<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CampaignToolPackage;
use App\Models\CampaignToolPayment;
use App\Models\CampaignToolRequest;
use Illuminate\Support\Collection;
use App\Models\CandidateCampaignToolEntitlement;

interface CampaignToolCommerceRepositoryInterface
{
    public function activePackagesForTools(array $toolIds): Collection;
    public function findActivePackage(int $packageId, int $toolId): CampaignToolPackage;
    public function createPackage(array $data): CampaignToolPackage;
    public function updatePackage(CampaignToolPackage $package, array $data): bool;
    public function deletePackage(CampaignToolPackage $package): bool;
    public function createPayment(array $data): CampaignToolPayment;
    public function lockedPaymentByReference(string $reference): ?CampaignToolPayment;
    public function lockedPaymentForRequest(CampaignToolRequest $request): CampaignToolPayment;
    public function createLedgerEntry(array $data): void;
    public function createEntitlement(array $data): void;
    public function hasActiveEntitlement(int $candidateId, int $toolId, ?string $toolKey = null): bool;
    public function lockedActiveEntitlement(int $candidateId, string $toolKey): ?CandidateCampaignToolEntitlement;
}
