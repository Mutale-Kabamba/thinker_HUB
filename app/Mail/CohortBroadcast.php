<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CohortBroadcast extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Course $course,
        public User $sender,
        public string $messageBody,
        public string $subjectLine,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            using: [
                static function ($message): void {
                    $headers = $message->getHeaders();
                    $headers->addTextHeader('X-Auto-Response-Suppress', 'All');
                    $headers->addTextHeader('Auto-Submitted', 'auto-generated');
                },
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cohort-broadcast',
        );
    }
}
