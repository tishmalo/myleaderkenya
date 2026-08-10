<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CampaignToolCommerceRepositoryInterface;
use App\Models\CandidateCampaignToolEntitlement;
use App\Models\CampaignToolFinancialLedger;
use App\Models\CampaignToolPackage;
use App\Models\CampaignToolPayment;
use App\Models\CampaignToolRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CampaignToolCommerceRepository implements CampaignToolCommerceRepositoryInterface
{
    public function activePackagesForTools(array $toolIds): Collection { return CampaignToolPackage::active()->whereIn('campaign_tool_id', $toolIds)->ordered()->get(); }
    public function findActivePackage(int $packageId, int $toolId): CampaignToolPackage { return CampaignToolPackage::active()->where('campaign_tool_id', $toolId)->findOrFail($packageId); }
    public function createPackage(array $data): CampaignToolPackage
    {
        return CampaignToolPackage::create($this->withLegacyPackageColumns($data));
    }

    public function updatePackage(CampaignToolPackage $package, array $data): bool
    {
        return $package->update($this->withLegacyPackageColumns($data));
    }
    public function deletePackage(CampaignToolPackage $package): bool { return $package->delete(); }
    public function createPayment(array $data): CampaignToolPayment { return CampaignToolPayment::create($data); }
    public function lockedPaymentByReference(string $reference): ?CampaignToolPayment { return CampaignToolPayment::with(['request.campaignTool','candidate','user'])->where('checkout_reference', $reference)->lockForUpdate()->first(); }
    public function lockedPaymentForRequest(CampaignToolRequest $request): CampaignToolPayment { return CampaignToolPayment::where('campaign_tool_request_id', $request->id)->lockForUpdate()->firstOrFail(); }
    public function createLedgerEntry(array $data): void { CampaignToolFinancialLedger::create($data); }
    public function createEntitlement(array $data): void { CandidateCampaignToolEntitlement::create($data); }
    public function hasActiveEntitlement(int $candidateId, int $toolId, ?string $toolKey = null): bool
    {
        return CandidateCampaignToolEntitlement::query()->where('candidate_id', $candidateId)->where('campaign_tool_id', $toolId)
            ->where('status', 'active')->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn ($query) => $query->whereNull('remaining_allowance')->orWhere('remaining_allowance', '>', 0))
            ->when($toolKey, fn ($query) => $query->where(fn ($inner) => $inner->whereNull('tool_key')->orWhere('tool_key', $toolKey)))->exists();
    }
    public function lockedActiveEntitlement(int $candidateId, string $toolKey): ?CandidateCampaignToolEntitlement
    {
        return CandidateCampaignToolEntitlement::query()->where('candidate_id',$candidateId)->where('status','active')
            ->where(fn($query)=>$query->where('tool_key',$toolKey)->orWhereHas('campaignTool',fn($tool)=>$tool->where('slug',$toolKey)))
            ->where(fn($query)=>$query->whereNull('expires_at')->orWhere('expires_at','>',now()))
            ->where(fn($query)=>$query->whereNull('remaining_allowance')->orWhere('remaining_allowance','>',0))
            ->oldest('activated_at')->lockForUpdate()->first();
    }

    private function withLegacyPackageColumns(array $data): array
    {
        if (Schema::hasColumn('campaign_tool_packages', 'price')) {
            $tokenValue = max(0.01, (float) config('campaign_tools.token_value_kes', 1));
            $data['price'] = round(((int) ($data['token_cost'] ?? 0)) * $tokenValue, 2);
        }

        if (Schema::hasColumn('campaign_tool_packages', 'currency')) {
            $data['currency'] = 'KES';
        }

        return $data;
    }
}
