<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CandidateSmsMessage;

interface CandidateSmsMessageRepositoryInterface
{
    public function create(array $data): CandidateSmsMessage;

    public function update(CandidateSmsMessage $message, array $data): bool;
}
