<?php

namespace Tests\Feature;

use App\Mail\StudentApprovedMail;
use App\Mail\SystemUpdateMail;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Notifications\AssignmentAssignedNotification;
use App\Notifications\CertificateIssuedNotification;
use App\Notifications\CourseEnrolledNotification;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResendAutomatedEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_mailer_transport_can_be_resolved(): void
    {
        config()->set('services.resend.key', 're_test_123456789');

        $mailer = Mail::mailer('resend');

        $this->assertNotNull($mailer->getSymfonyTransport());
    }

    public function test_all_automated_thinker_hub_emails_render_with_branded_templates(): void
    {
        $student = User::factory()->create([
            'name' => 'Chanda Banda',
            'email' => 'chanda@example.com',
            'role' => 'student',
        ]);

        $course = Course::create([
            'title' => 'Applied AI & Web Engineering',
            'code' => 'AI-201',
            'slug' => 'applied-ai-web-engineering',
            'is_active' => true,
        ]);

        // 1. Email Verification
        $verifyMail = (new QueuedVerifyEmail)->toMail($student);
        $verifyHtml = (string) $verifyMail->render();
        $this->assertStringContainsString('Verify Your Email Address', $verifyHtml);
        $this->assertStringContainsString('Chanda', $verifyHtml);
        $this->assertStringContainsString('thinkerhub.online/images/logos/green.png', $verifyHtml);

        // 2. Password Reset
        $resetMail = (new ResetPassword('sample-reset-token'))->toMail($student);
        $resetHtml = (string) $resetMail->render();
        $this->assertStringContainsString('Reset Your Password', $resetHtml);
        $this->assertStringContainsString('sample-reset-token', $resetHtml);

        // 3. Assignment Assigned
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Build an AI Study Helper',
            'description' => 'Create a simple prompt workflow.',
            'due_date' => now()->addDays(5),
        ]);
        $assignmentNotification = new AssignmentAssignedNotification($assignment, $course->title);
        $assignmentHtml = (string) $assignmentNotification->toMail($student)->render();
        $this->assertStringContainsString('Build an AI Study Helper', $assignmentHtml);
        $this->assertStringContainsString('Applied AI &amp; Web Engineering', $assignmentHtml);

        // 4. Course Enrollment Confirmation (database + mail)
        $enrolledNotification = new CourseEnrolledNotification($course);
        $this->assertSame(['database', 'mail'], $enrolledNotification->via($student));
        $enrolledHtml = (string) $enrolledNotification->toMail($student)->render();
        $this->assertStringContainsString('Applied AI &amp; Web Engineering', $enrolledHtml);
        $this->assertStringContainsString('START LEARNING NOW', $enrolledHtml);

        // 5. Certificate Issued (database + mail)
        $certificate = new Certificate([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'verification_code' => 'TH-2026-999',
        ]);
        $certificate->setRelation('course', $course);
        $certNotification = new CertificateIssuedNotification($certificate);
        $this->assertSame(['database', 'mail'], $certNotification->via($student));
        $certHtml = (string) $certNotification->toMail($student)->render();
        $this->assertStringContainsString('TH-2026-999', $certHtml);
        $this->assertStringContainsString('Congratulations, Chanda!', $certHtml);

        // 6. Registration / Student Account Approved
        $approvedHtml = (new StudentApprovedMail($student, $course))->render();
        $this->assertStringContainsString('Welcome to think.er HUB, Chanda!', $approvedHtml);
        $this->assertStringContainsString('AI-201', $approvedHtml);

        // 7. System Update Mailable
        $updateHtml = (new SystemUpdateMail(
            updateTitle: 'Offline Learning Mode Is Live',
            updateSubtitle: 'Study anywhere, even without internet.',
            intro: 'We just shipped offline course support.',
            highlights: [
                ['title' => 'Offline Sync', 'description' => 'Syncs progress automatically.'],
            ],
            firstName: 'Chanda',
        ))->render();
        $this->assertStringContainsString('Offline Learning Mode Is Live', $updateHtml);
        $this->assertStringContainsString('Offline Sync', $updateHtml);
    }
}
