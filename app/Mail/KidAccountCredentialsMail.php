<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KidAccountCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $parent,
        public User $kid,
        public Course $course,
        public string $uniqueCode,
        public ?string $relationship = 'Parent',
        public ?string $plainPassword = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'think.er HUB: Kid Account Login Code & Enrollment Confirmation - ' . $this->kid->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.kid-account-credentials',
        );
    }
}
