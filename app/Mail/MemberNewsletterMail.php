<?php

namespace App\Mail;

use App\Models\MemberNewsletterCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class MemberNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MemberNewsletterCampaign $campaign,
        public User $recipient,
        public string $unsubscribeUrl,
        public bool $isTest = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->campaign->subject;
        if ($this->isTest) {
            $subject = '[TEST] ' . $subject;
        }

        return new Envelope(
            from: new Address(
                (string) config('newsletter.from_address'),
                (string) config('newsletter.from_name'),
            ),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.member-newsletter',
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<' . $this->unsubscribeUrl . '>',
            ],
        );
    }
}
