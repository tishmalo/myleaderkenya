<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Models\Candidate;
use App\Notifications\CandidateClaimLinkNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Issues the one-time claim token that lets an aspirant take ownership of a
 * profile an administrator created for them.
 */
class CandidateClaimLinkService
{
    private const TOKEN_LENGTH = 64;

    private const TOKEN_TTL_DAYS = 7;

    public function __construct(
        private CandidateRepositoryInterface $candidates
    ) {}

    /**
     * Send the claim link, or return the warning to flash when it cannot be sent.
     */
    public function send(Candidate $candidate): ?string
    {
        if ($candidate->user_id || $candidate->claimed_at) {
            return 'This aspirant account has already been claimed.';
        }

        if (blank($candidate->email)) {
            return 'Add an email address before sending a claim link.';
        }

        $token = Str::random(self::TOKEN_LENGTH);
        $expiresAt = now()->addDays(self::TOKEN_TTL_DAYS);

        $this->candidates->storeClaimToken($candidate, hash('sha256', $token), $expiresAt);

        Notification::route('mail', $candidate->email)
            ->notify(new CandidateClaimLinkNotification(
                $candidate->name,
                route('aspirants.claim.show', [$candidate, $token]),
                $expiresAt
            ));

        return null;
    }
}
