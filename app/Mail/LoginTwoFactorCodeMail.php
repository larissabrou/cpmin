<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginTwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        $brand = config('aggregpay.brand', 'AggregPay');

        return new Envelope(
            subject: "Code de connexion — {$brand}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login-two-factor-code',
        );
    }
}
