<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public Course $course,
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        $rawName = trim((string) ($this->student->name ?? ''));
        $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';

        return new Envelope(
            subject: 'think.er HUB: Payment Receipt - ' . $this->payment->reference . ' (' . $this->course->code . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-receipt',
            text: 'emails.payment-receipt-text',
        );
    }
}
