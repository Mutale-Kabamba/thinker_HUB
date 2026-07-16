<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\StudentSubmissionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationMailPersonalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_notifications_include_recipient_and_signer_names(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Jane',
            'role' => 'admin',
        ]);

        $recipient = User::factory()->create([
            'name' => 'Instructor Mark',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $notification = new StudentSubmissionNotification(
            studentName: 'Student John',
            submissionType: 'assignment',
            itemTitle: 'Database Basics',
            itemId: 1,
        );

        $mail = $notification->toMail($recipient);

        $this->assertSame('New Submission from Student John', $mail->subject);
        $this->assertSame('Instructor Mark', $mail->viewData['recipientName'] ?? null);
        $this->assertSame('Admin Jane', $mail->viewData['signerName'] ?? null);
    }
}
