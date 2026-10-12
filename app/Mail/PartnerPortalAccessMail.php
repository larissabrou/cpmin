<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerPortalAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public User $user,
        public string $plainPassword,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        $brand = config('aggregpay.brand', 'AggregPay');

        return new Envelope(
            subject: "Vos accès au portail {$brand} — {$this->company->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-portal-access',
        );
    }
}
