<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationEmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_uses_personalized_greeting_and_signer(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'name' => 'Admin Jane',
            'role' => 'admin',
        ]);

        $student = User::factory()->unverified()->create([
            'name' => 'Student John',
            'role' => 'student',
        ]);

        $this->actingAs($admin);
        $student->sendEmailVerificationNotification();

        Notification::assertSentTo($student, QueuedVerifyEmail::class, function (QueuedVerifyEmail $notification, array $channels) use ($student) {
            $mail = $notification->toMail($student);

            return $mail->greeting === 'Hello, Student John'
                && $mail->salutation === "Regards,\nAdmin Jane"
                && $mail->subject === 'Verify Email Address';
        });
    }
}
