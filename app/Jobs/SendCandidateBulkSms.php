<?php

namespace App\Jobs;

use App\Contracts\Repositories\Web\CandidateSmsMessageRepositoryInterface;
use App\Models\CandidateSmsMessage;
use App\Models\User;
use App\Services\Sms\InfobipSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendCandidateBulkSms implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private int $smsMessageId) {}

    public function handle(
        InfobipSmsService $smsService,
        CandidateSmsMessageRepositoryInterface $messages
    ): void {
        $smsMessage = CandidateSmsMessage::with('candidate.smsSetting')->find($this->smsMessageId);

        if (! $smsMessage) {
            Log::warning('Bulk SMS job skipped because the SMS log was not found.', [
                'sms_message_id' => $this->smsMessageId,
            ]);

            return;
        }

        $setting = $smsMessage->candidate?->smsSetting;

        if (! $setting || ! $setting->isReady()) {
            Log::warning('Bulk SMS job failed because candidate SMS settings are incomplete.', [
                'sms_message_id' => $smsMessage->id,
                'candidate_id' => $smsMessage->candidate_id,
            ]);

            $messages->update($smsMessage, [
                'status' => 'failed',
                'provider_response' => ['message' => 'Candidate Bulk SMS settings are incomplete.'],
            ]);

            return;
        }

        $messages->update($smsMessage, ['status' => 'processing']);
        Log::info('Bulk SMS job processing started.', [
            'sms_message_id' => $smsMessage->id,
            'candidate_id' => $smsMessage->candidate_id,
            'scope_type' => $smsMessage->scope_type,
            'scope_column' => $smsMessage->scope_column,
            'scope_value' => $smsMessage->scope_value,
        ]);

        $recipients = $this->recipients($smsMessage);
        Log::info('Bulk SMS recipients resolved from voter scope.', [
            'sms_message_id' => $smsMessage->id,
            'candidate_id' => $smsMessage->candidate_id,
            'recipient_count' => $recipients->count(),
            'valid_phone_count' => $recipients->filter(fn (User $user): bool => filled($user->phone))->count(),
        ]);

        try {
            $result = $smsService->sendBulk($setting, $recipients, $smsMessage->message);

            $messages->update($smsMessage, [
                'recipient_count' => $result['recipient_count'],
                'status' => $result['success'] ? 'accepted' : 'failed',
                'provider_response' => $result,
            ]);

            Log::info('Bulk SMS provider request completed.', [
                'sms_message_id' => $smsMessage->id,
                'candidate_id' => $smsMessage->candidate_id,
                'status' => $result['success'] ? 'accepted' : 'failed',
                'recipient_count' => $result['recipient_count'],
            ]);
        } catch (RequestException $exception) {
            $messages->update($smsMessage, [
                'status' => 'failed',
                'provider_response' => $exception->response?->json() ?? ['message' => $exception->getMessage()],
            ]);

            Log::warning('Bulk SMS provider rejected the request.', [
                'sms_message_id' => $smsMessage->id,
                'candidate_id' => $smsMessage->candidate_id,
                'status_code' => $exception->response?->status(),
                'error' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            $messages->update($smsMessage, [
                'status' => 'failed',
                'provider_response' => ['message' => $exception->getMessage()],
            ]);

            Log::error('Bulk SMS job failed unexpectedly.', [
                'sms_message_id' => $smsMessage->id,
                'candidate_id' => $smsMessage->candidate_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function recipients(CandidateSmsMessage $smsMessage)
    {
        return User::query()
            ->where(function (Builder $query): void {
                $query->where('is_voter', true)
                    ->orWhere('is_registered', true);
            })
            ->when(
                $smsMessage->scope_column && $smsMessage->scope_value,
                fn (Builder $query): Builder => $query->where($smsMessage->scope_column, $smsMessage->scope_value)
            )
            ->whereNotNull('phone')
            ->select('id', 'phone')
            ->get();
    }
}
