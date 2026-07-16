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
        $prefix = $this->requiresPaymentApproval
            ? 'Student registration requires approval'
            : 'Student registration received';

        $subject = $prefix.' - '.$this->student->name;

        $listUnsubscribe = trim((string) config('mail.deliverability.list_unsubscribe'));
        $listUnsubscribePost = trim((string) config('mail.deliverability.list_unsubscribe_post', 'List-Unsubscribe=One-Click'));

        return new Envelope(
            subject: $subject,
            using: [
                static function (Email $message) use ($listUnsubscribe, $listUnsubscribePost): void {
                    $headers = $message->getHeaders();

                    $headers->addTextHeader('X-Auto-Response-Suppress', 'All');
                    $headers->addTextHeader('Auto-Submitted', 'auto-generated');

                    if ($listUnsubscribe !== '') {
                        $headers->addTextHeader('List-Unsubscribe', $listUnsubscribe);
                        $headers->addTextHeader('List-Unsubscribe-Post', $listUnsubscribePost !== '' ? $listUnsubscribePost : 'List-Unsubscribe=One-Click');
                    }
                },
            ],
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
