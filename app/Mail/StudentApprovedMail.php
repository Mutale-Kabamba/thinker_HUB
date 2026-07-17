<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class StudentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $actionUrl;

    public string $actionLabel;

    public function __construct(
        public User $student,
        public ?Course $course = null,
    ) {
        $this->buildAction();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your account is now active',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-approved',
        );
    }

    private function buildAction(): void
    {
        if (filled($this->student->firebase_uid)) {
            $this->actionLabel = 'Sign In To Your Account';
            $this->actionUrl = route('login', ['email' => $this->student->email]);

            return;
        }

        if (blank($this->student->pending_login_password)) {
            $this->actionLabel = 'Sign In To Your Account';
            $this->actionUrl = route('login');

            return;
        }

        $token = Str::random(64);
        $expiresAt = now()->addDays(7);

        $this->student->forceFill([
            'pending_login_token' => $token,
            'pending_login_token_expires_at' => $expiresAt,
            'pending_login_token_used_at' => null,
        ])->save();

        $this->actionLabel = 'Open Prefilled Sign In';
        $this->actionUrl = URL::temporarySignedRoute(
            'auth.student-approval.manual',
            $expiresAt,
            [
                'user' => $this->student->id,
                'token' => $token,
            ],
        );
    }
}
