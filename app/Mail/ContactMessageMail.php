<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $contactSubject,
        public string $bodyText,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Contact] '.$this->contactSubject.' - '.$this->name,
            replyTo: [new Address($this->email, $this->name)],
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
            view: 'emails.contact-message',
            with: [
                'subject' => $this->contactSubject,
                'contactSubject' => $this->contactSubject,
            ],
        );
    }
}
