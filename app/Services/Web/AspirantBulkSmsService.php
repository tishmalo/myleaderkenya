<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CandidateSmsMessageRepositoryInterface;
use App\Jobs\SendCandidateBulkSms;
use App\Models\Candidate;
use App\Models\CandidateSmsMessage;
use App\Models\User;
use App\Support\BulkSmsSendResult;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AspirantBulkSmsService
{
    public function __construct(
        private AspirantWorkspaceService $workspace,
        private AspirantTokenService $tokens,
        private CandidateBulkSmsContactService $contacts,
        private CandidateSmsMessageRepositoryInterface $messages,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $scope
     */
    public function send(Candidate $candidate, User $user, array $validated, array $scope): BulkSmsSendResult
    {
        $recipientSource = $validated['recipient_source'];
        $classificationId = isset($validated['support_group_type_id'])
            ? (int) $validated['support_group_type_id']
            : null;

        if ($recipientSource === 'registered_voters' && $scope['missing']) {
            return BulkSmsSendResult::warning((string) $scope['message']);
        }

        $recipientCount = $this->recipientCount($candidate, $recipientSource, $scope, $classificationId);
        $quote = $this->tokens->quoteBulkSms($validated['message'], $recipientCount);

        if ($quote['sms_units'] <= 0) {
            return BulkSmsSendResult::warning($recipientSource === 'uploaded_contacts'
                ? 'No uploaded contacts with valid phone numbers were found in that classification.'
                : 'No valid phone numbers were found for your scoped voters.');
        }

        try {
            $reservation = $this->tokens->reserveBulkSms($candidate, $user, $quote);
        } catch (RuntimeException $exception) {
            return BulkSmsSendResult::warning($exception->getMessage() . ' Buy more tokens to continue.');
        }

        try {
            $smsMessage = $this->messages->create([
                'candidate_id' => $candidate->id,
                'user_id' => $user->id,
                'message' => $validated['message'],
                'recipient_source' => $recipientSource,
                'support_group_type_id' => $classificationId,
                'privacy_acknowledged_at' => now(),
                'scope_type' => $recipientSource === 'registered_voters' ? $scope['type'] : 'uploaded_contacts',
                'scope_column' => $recipientSource === 'registered_voters' ? $scope['column'] : null,
                'scope_value' => $recipientSource === 'registered_voters' ? $scope['value'] : null,
                'recipient_count' => $recipientCount,
                'status' => 'queued',
                'token_transaction_id' => $reservation->id,
                'sms_character_count' => $quote['character_count'],
                'sms_encoding' => $quote['encoding'],
                'sms_segment_count' => $quote['segment_count'],
                'sms_unit_count' => $quote['sms_units'],
                'token_cost' => $quote['tokens_required'],
            ]);

            $this->tokens->attachReservationToSms($reservation, $smsMessage);
        } catch (RuntimeException) {
            $this->tokens->refundReservation($reservation, 'Bulk SMS could not be queued.');

            return BulkSmsSendResult::warning('Bulk SMS could not be queued. Your reserved tokens were refunded.');
        }

        SendCandidateBulkSms::dispatch($smsMessage->id);

        Log::info('Bulk SMS queued from aspirant workspace.', [
            'sms_message_id' => $smsMessage->id,
            'candidate_id' => $candidate->id,
            'user_id' => $user->id,
            'recipient_count' => $recipientCount,
            'sms_units' => $quote['sms_units'],
            'token_cost' => $quote['tokens_required'],
        ]);

        return BulkSmsSendResult::sent($recipientCount, (int) $quote['tokens_required'], (int) $quote['sms_units']);
    }

    /**
     * @param  array<string, mixed>  $scope
     */
    private function recipientCount(Candidate $candidate, string $recipientSource, array $scope, ?int $classificationId): int
    {
        if ($recipientSource === 'uploaded_contacts') {
            return $this->contacts->recipientCount($candidate, $classificationId);
        }

        return $this->workspace->registeredVotersQuery($scope)->whereNotNull('phone')->count();
    }
}
