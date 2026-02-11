<?php

namespace App\Mail;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Purchase $purchase,
        public User $buyer,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '取引が完了しました',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction-completed',
        );
    }
}
