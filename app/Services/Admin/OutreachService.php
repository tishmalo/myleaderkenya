<?php

namespace App\Services\Admin;

use App\Mail\OutreachMail;
use App\Models\OutreachCampaign;
use App\Models\OutreachRecipient;
use App\Models\User;
use App\Services\Sms\PlatformSmsSender;
use App\Support\MessageTemplate;
use App\Support\OutreachAudience;
use App\Support\OutreachRecipientData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Admin outreach: builds a campaign for an audience, one row per recipient, then
 * sends it and records what happened to every person.
 */
class OutreachService
{
    public function __construct(
        private OutreachAudienceResolver $audiences,
        private PlatformSmsSender $sms,
    ) {}

    /**
     * @return Collection<int, OutreachRecipientData>
     */
    public function recipientsFor(string $channel, string $audience): Collection
    {
        return $this->audiences->resolve($audience)
            ->filter(fn (OutreachRecipientData $recipient): bool => $recipient->hasChannel($channel))
            ->unique(fn (OutreachRecipientData $recipient): string => $this->dedupeKey($recipient, $channel))
            ->values();
    }

    /**
     * @param  array{audience: string, subject?: ?string, body: string}  $data
     */
    public function create(string $channel, array $data, User $admin): OutreachCampaign
    {
        $recipients = $this->recipientsFor($channel, $data['audience']);

        if ($recipients->isEmpty()) {
            throw ValidationException::withMessages([
                'audience' => $channel === OutreachCampaign::CHANNEL_EMAIL
                    ? 'Nobody in that audience has an email address, so there is nothing to send.'
                    : 'Nobody in that audience has a phone number, so there is nothing to send.',
            ]);
        }

        $campaign = OutreachCampaign::create([
            'channel' => $channel,
            'audience' => $data['audience'],
            'subject' => $channel === OutreachCampaign::CHANNEL_EMAIL ? trim((string) ($data['subject'] ?? '')) : null,
            'body' => $data['body'],
            'status' => OutreachCampaign::STATUS_QUEUED,
            'total_recipients' => $recipients->count(),
            'created_by' => $admin->getKey(),
        ]);

        foreach ($recipients as $recipient) {
            $variables = $recipient->variables + [
                'name' => (string) $recipient->name,
                'email' => (string) $recipient->email,
                'phone' => (string) $recipient->phone,
            ];

            OutreachRecipient::create([
                'campaign_id' => $campaign->getKey(),
                'recipient_type' => $recipient->morphType,
                'recipient_id' => $recipient->morphId,
                'name' => $recipient->name,
                'email' => $recipient->email,
                'phone' => $recipient->phone,
                'status' => OutreachRecipient::STATUS_PENDING,
                'rendered_subject' => $campaign->isEmail()
                    ? MessageTemplate::render((string) $campaign->subject, $variables)
                    : null,
                'rendered_body' => MessageTemplate::render((string) $campaign->body, $variables),
            ]);
        }

        return $campaign;
    }

    /**
     * Sends every pending recipient and records the outcome per person.
     */
    public function send(OutreachCampaign $campaign): OutreachCampaign
    {
        $campaign->forceFill([
            'status' => OutreachCampaign::STATUS_SENDING,
            'started_at' => $campaign->started_at ?? now(),
        ])->save();

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        $campaign->recipients()
            ->where('status', OutreachRecipient::STATUS_PENDING)
            ->cursor()
            ->each(function (OutreachRecipient $recipient) use ($campaign, &$sent, &$failed, &$skipped): void {
                try {
                    $this->deliver($campaign, $recipient);

                    $recipient->forceFill([
                        'status' => OutreachRecipient::STATUS_SENT,
                        'sent_at' => now(),
                        'error' => null,
                    ])->save();

                    $sent++;
                } catch (Throwable $exception) {
                    $recipient->forceFill([
                        'status' => OutreachRecipient::STATUS_FAILED,
                        'error' => mb_substr($exception->getMessage(), 0, 500),
                    ])->save();

                    $failed++;
                }
            });

        $campaign->forceFill([
            'status' => $sent === 0 && $failed > 0
                ? OutreachCampaign::STATUS_FAILED
                : OutreachCampaign::STATUS_SENT,
            'sent_count' => $sent,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
            'finished_at' => now(),
        ])->save();

        return $campaign->refresh();
    }

    /**
     * @throws RuntimeException
     */
    private function deliver(OutreachCampaign $campaign, OutreachRecipient $recipient): void
    {
        if ($campaign->isEmail()) {
            if (blank($recipient->email)) {
                throw new RuntimeException('No email address on this record.');
            }

            Mail::to($recipient->email)->send(new OutreachMail(
                (string) $recipient->rendered_subject,
                (string) $recipient->rendered_body,
            ));

            return;
        }

        if (blank($recipient->phone)) {
            throw new RuntimeException('No phone number on this record.');
        }

        $result = $this->sms->send((string) $recipient->phone, (string) $recipient->rendered_body);

        if (! ($result['success'] ?? false)) {
            throw new RuntimeException((string) ($result['message'] ?? 'The SMS provider rejected the message.'));
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function audiences(): array
    {
        return OutreachAudience::all();
    }

    private function dedupeKey(OutreachRecipientData $recipient, string $channel): string
    {
        if ($channel === OutreachCampaign::CHANNEL_EMAIL) {
            return 'email:'.mb_strtolower(trim((string) $recipient->email));
        }

        return 'phone:'.(preg_replace('/\D+/', '', (string) $recipient->phone) ?? '');
    }
}
