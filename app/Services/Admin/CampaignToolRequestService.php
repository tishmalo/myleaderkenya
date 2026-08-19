<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignToolRequest;
use App\Models\User;
use App\Services\Web\CampaignToolCommerceService;
use App\Services\Web\DonorToolboxService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CampaignToolRequestService
{
    public function __construct(
        private CampaignToolRequestRepositoryInterface $requests,
        private CampaignToolCommerceService $commerce,
        private DonorToolboxService $toolbox
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->requests->paginate($filters);
    }

    public function getToolOptions(): Collection
    {
        return $this->requests->toolOptions();
    }

    public function update(CampaignToolRequest $request, User $admin, array $data): void
    {
        if ($request->fulfilment_type === 'paid_package') {
            $this->commerce->transition($request, $admin, $data['action'], $data['admin_notes'] ?? null);

            return;
        }

        if (
            $request->request_type === 'adoption'
            && $data['action'] === 'reject'
            && $request->payment_status === 'paid'
        ) {
            $this->toolbox->refundAdoption($request, 'Sponsorship cancelled by an administrator.');
            $this->requests->update($request, ['status' => 'cancelled', 'admin_notes' => $data['admin_notes'] ?? null]);

            return;
        }

        $status = match ($data['action']) {
            'activate' => 'completed',
            'start_fulfilment' => 'in_progress',
            default => 'cancelled',
        };

        $this->requests->update($request, ['status' => $status, 'admin_notes' => $data['admin_notes'] ?? null]);
    }

    public function canDelete(CampaignToolRequest $request): bool
    {
        return $request->request_type !== 'adoption';
    }

    public function delete(CampaignToolRequest $request): bool
    {
        return $this->requests->delete($request);
    }
}
