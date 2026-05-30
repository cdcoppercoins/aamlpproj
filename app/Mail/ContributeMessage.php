<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContributeMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $messageText,
        public string $ip,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('contribute.mail_from_address'),
                config('contribute.mail_from_name'),
            ),
            replyTo: [
                new Address($this->senderEmail, $this->senderName),
            ],
            subject: 'MiniLicensePlates.com Contribution',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contribute',
        );
    }
}
