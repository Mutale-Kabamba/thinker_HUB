<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class NewStudentRegistrationAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, string> $instructorContacts
     */
    public function __construct(
        public User $student,
        public Course $course,
        public bool $requiresPaymentApproval,
        public array $instructorContacts = [],
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = sprintf(
            'think.er HUB: New Student Registration - %s (%s)',
            $this->student->name,
            $this->course->code
        );

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-student-registration-alert',
            text: 'emails.new-student-registration-alert-text',
        );
    }
}
