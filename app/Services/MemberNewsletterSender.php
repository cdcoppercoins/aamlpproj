<?php

namespace App\Services;

use App\Mail\MemberNewsletterMail;
use App\Models\MemberNewsletterCampaign;
use App\Models\MemberNewsletterDelivery;
use App\Models\User;
use App\Support\MemberNewsletterRecipients;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class MemberNewsletterSender
{
    public function assertMailConfigured(): void
    {
        if (config('mail.default') === 'log' && app()->environment('production')) {
            throw new RuntimeException(
                'Mail is set to log only. Configure MAIL_MAILER on the server before sending newsletters.'
            );
        }
    }

    public function sendTest(MemberNewsletterCampaign $campaign, User $admin): void
    {
        $this->assertMailConfigured();

        Mail::to($admin->email)->send(new MemberNewsletterMail(
            campaign: $campaign,
            recipient: $admin,
            unsubscribeUrl: $this->unsubscribeUrlFor($admin),
            isTest: true,
        ));

        $campaign->update(['test_sent_at' => now()]);
    }

    public function queueCampaign(MemberNewsletterCampaign $campaign, User $admin): int
    {
        if (! $campaign->isEditable()) {
            throw new RuntimeException('Only draft campaigns can be sent.');
        }

        $this->assertMailConfigured();

        $recipientIds = MemberNewsletterRecipients::eligibleQuery()
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($recipientIds === []) {
            throw new RuntimeException('No eligible members to receive this newsletter.');
        }

        DB::transaction(function () use ($campaign, $admin, $recipientIds): void {
            $now = now();

            foreach (array_chunk($recipientIds, 500) as $chunk) {
                $rows = [];
                foreach ($chunk as $userId) {
                    $rows[] = [
                        'campaign_id' => $campaign->id,
                        'user_id' => $userId,
                        'status' => MemberNewsletterDelivery::STATUS_PENDING,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                MemberNewsletterDelivery::query()->insert($rows);
            }

            $campaign->update([
                'status' => MemberNewsletterCampaign::STATUS_SENDING,
                'sent_by' => $admin->id,
                'recipient_count' => count($recipientIds),
                'sent_count' => 0,
                'failed_count' => 0,
                'skipped_count' => 0,
                'send_started_at' => $now,
                'send_completed_at' => null,
            ]);
        });

        return count($recipientIds);
    }

    /**
     * @return array{sent: int, failed: int, pending: int, done: bool}
     */
    public function sendNextBatch(MemberNewsletterCampaign $campaign): array
    {
        if (! $campaign->isSending()) {
            throw new RuntimeException('This campaign is not currently sending.');
        }

        $this->assertMailConfigured();

        $batchSize = max(1, (int) config('newsletter.batch_size', 15));
        $deliveries = $campaign->deliveries()
            ->with('user')
            ->where('status', MemberNewsletterDelivery::STATUS_PENDING)
            ->orderBy('id')
            ->limit($batchSize)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($deliveries as $delivery) {
            $user = $delivery->user;

            if ($user === null || ! $user->receivesMemberNewsletter()) {
                $delivery->update([
                    'status' => MemberNewsletterDelivery::STATUS_SKIPPED,
                    'error_message' => 'Member is blocked or unsubscribed.',
                ]);
                $campaign->increment('skipped_count');

                continue;
            }

            try {
                Mail::to($user->email)->send(new MemberNewsletterMail(
                    campaign: $campaign,
                    recipient: $user,
                    unsubscribeUrl: $this->unsubscribeUrlFor($user),
                    isTest: false,
                ));

                $delivery->update([
                    'status' => MemberNewsletterDelivery::STATUS_SENT,
                    'sent_at' => now(),
                    'error_message' => null,
                ]);
                $campaign->increment('sent_count');
                $sent++;
            } catch (\Throwable $e) {
                $delivery->update([
                    'status' => MemberNewsletterDelivery::STATUS_FAILED,
                    'error_message' => mb_substr($e->getMessage(), 0, 2000),
                ]);
                $campaign->increment('failed_count');
                $failed++;
            }
        }

        $campaign->refresh();
        $pending = $campaign->pendingDeliveryCount();

        if ($pending === 0) {
            $finalStatus = $campaign->failed_count > 0 && $campaign->sent_count === 0
                ? MemberNewsletterCampaign::STATUS_FAILED
                : MemberNewsletterCampaign::STATUS_SENT;

            $campaign->update([
                'status' => $finalStatus,
                'send_completed_at' => now(),
            ]);
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'done' => $pending === 0,
        ];
    }

    public function cancelRemaining(MemberNewsletterCampaign $campaign): int
    {
        if (! $campaign->isSending()) {
            throw new RuntimeException('Only a campaign that is sending can be cancelled.');
        }

        $skipped = $campaign->deliveries()
            ->where('status', MemberNewsletterDelivery::STATUS_PENDING)
            ->update([
                'status' => MemberNewsletterDelivery::STATUS_SKIPPED,
                'error_message' => 'Send cancelled by admin.',
            ]);

        $campaign->increment('skipped_count', $skipped);
        $campaign->update([
            'status' => MemberNewsletterCampaign::STATUS_CANCELLED,
            'send_completed_at' => now(),
        ]);

        return $skipped;
    }

    public function unsubscribeUrlFor(User $user): string
    {
        return URL::signedRoute('newsletter.member-unsubscribe', ['user' => $user->id]);
    }
}
