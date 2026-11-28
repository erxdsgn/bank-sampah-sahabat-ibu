<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $username,
        public string $verifyUrl
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Email Akun Bank Sampah Gemi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirm-email',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
