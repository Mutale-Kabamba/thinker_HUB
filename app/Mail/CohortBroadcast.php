<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;

class CohortBroadcast extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Course $course,
        public User $sender,
        public string $messageBody,
        public string $subjectLine,
        public ?string $attachmentPath = null,
        public ?string $attachmentName = null,
        public ?string $attachmentMime = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cohort-broadcast',
            text: 'emails.cohort-broadcast-text',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (blank($this->attachmentPath)) {
            return [];
        }

        $disk = Storage::disk('public');

        if ($disk->exists($this->attachmentPath)) {
            $attachment = Attachment::fromStorageDisk('public', $this->attachmentPath);

            if (filled($this->attachmentName)) {
                $attachment->as($this->attachmentName);
            }

            if (filled($this->attachmentMime)) {
                $attachment->withMime($this->attachmentMime);
            }

            return [$attachment];
        }

        return [];
    }
}
