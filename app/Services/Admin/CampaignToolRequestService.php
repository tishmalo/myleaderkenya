<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignToolRequest;
use App\Models\User;
use App\Services\Web\CampaignToolCommerceService;
use App\Services\Web\DonorToolboxService;
use App\Services\Web\SpamFilterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CampaignToolRequestService
{
    public function __construct(
        private CampaignToolRequestRepositoryInterface $requests,
        private CampaignToolCommerceService $commerce,
        private DonorToolboxService $toolbox,
        private SpamFilterService $spamFilter
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
        if ($data['action'] === 'mark_spam') {
            $this->requests->update($request, [
                'is_spam' => true,
                'spam_reason' => $data['spam_reason'] ?? 'marked_by_admin',
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);

            $this->spamFilter->recordSample(
                $this->samplePayload($request),
                'reported_by_admin',
                null,
                'reported',
                $request->id
            );

            return;
        }

        if ($data['action'] === 'unmark_spam') {
            $this->requests->update($request, [
                'is_spam' => false,
                'spam_reason' => null,
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);

            return;
        }

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

    public function reportSpam(CampaignToolRequest $request): void
    {
        $this->requests->update($request, [
            'is_spam' => true,
            'spam_reason' => 'reported_by_admin',
        ]);

        $this->spamFilter->recordSample(
            $this->samplePayload($request),
            'reported_by_admin',
            null,
            'reported',
            $request->id
        );
    }

    public function canDelete(CampaignToolRequest $request): bool
    {
        return $request->request_type !== 'adoption';
    }

    public function delete(CampaignToolRequest $request): bool
    {
        return $this->requests->delete($request);
    }

    private function samplePayload(CampaignToolRequest $request): array
    {
        return [
            'requester_name' => $request->requester_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'requested_feature' => $request->requested_feature,
            'use_case' => $request->use_case,
        ];
    }
}