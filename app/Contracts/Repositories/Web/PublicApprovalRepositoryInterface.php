<?php

namespace App\Contracts\Repositories\Web;

interface PublicApprovalRepositoryInterface
{
    public function approvalForProfile(string $profileSlug): ?float;
}
