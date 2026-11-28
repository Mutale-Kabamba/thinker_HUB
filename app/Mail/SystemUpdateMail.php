<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string|array{title: string, description?: string}>  $highlights
     */
    public function __construct(
        public string $updateTitle,
        public string $updateSubtitle = '',
        public string $intro = '',
        public array $highlights = [],
        public ?string $firstName = null,
        public ?string $updateDate = null,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public ?string $closingNote = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'think.er HUB Update: '.$this->updateTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system-update',
        );
    }
}
