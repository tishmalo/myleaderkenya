<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateSmsMessageRepositoryInterface;
use App\Models\CandidateSmsMessage;

class CandidateSmsMessageRepository implements CandidateSmsMessageRepositoryInterface
{
    public function create(array $data): CandidateSmsMessage
    {
        return CandidateSmsMessage::create($data);
    }

    public function update(CandidateSmsMessage $message, array $data): bool
    {
        return $message->update($data);
    }
}
