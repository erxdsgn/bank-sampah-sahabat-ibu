<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    // 1. Terima variabel $otp saat class dipanggil
    public function __construct(public string $otp)
    {
        //
    }

    // 2. Ubah judul (subject) email yang masuk ke kotak masuk user
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Reset Password',
        );
    }

    // 3. Arahkan ke file Blade tampilan email (resources/views/emails/otp.blade.php)
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
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
