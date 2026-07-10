<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class BulkMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $htmlContent,
        public readonly string $plainContent,
        public readonly ?string $unsubscribeUrl = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [config('mail.from.address')],
            subject: __('Metarang Information Service'),
            from: new Address(config('mail.from.address'), ' متارنگ | Metarang'),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlContent,
            text: 'mail.bulk-message-text',
            with: [
                'plainContent' => $this->plainContent,
            ],
        );
    }

    public function headers(): Headers
    {
        $unsubscribeUrl = $this->unsubscribeUrl ?? url('/unsubscribe');

        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.$unsubscribeUrl.'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
