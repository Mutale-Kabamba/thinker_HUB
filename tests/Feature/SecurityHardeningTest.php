<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Services\Payment\BroadPayGateway;
use App\Support\DocumentResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_http_security_headers_are_applied_to_public_responses(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(self)');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
        $this->assertStringContainsString("frame-ancestors 'self'", (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_student_cannot_self_enroll_via_profile_sync_enrollments_endpoint(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $course = Course::create([
            'title' => 'Paid Cybersecurity Bootcamp',
            'code' => 'SEC-101',
            'overview' => 'Security bootcamp',
            'fees' => '2500',
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->put(route('profiles.enrollments.sync', $student), [
                'course_ids' => [$course->id],
            ])
            ->assertForbidden();

        $this->assertFalse($student->courses()->where('courses.id', $course->id)->exists());

        // Verify admin can still sync enrollments
        $admin = User::factory()->create([
            'role' => 'admin',
            'track' => 'Advanced',
        ]);

        $this->actingAs($admin)
            ->put(route('profiles.enrollments.sync', $student), [
                'course_ids' => [$course->id],
            ])
            ->assertRedirect();

        $this->assertTrue($student->courses()->where('courses.id', $course->id)->exists());
    }

    public function test_student_cannot_escalate_default_portal_to_admin(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'default_portal' => 'student',
            'track' => 'Beginner',
        ]);

        $this->actingAs($student)
            ->patch(route('profile.update'), [
                'name' => 'Updated Student',
                'email' => $student->email,
                'default_portal' => 'admin',
            ])
            ->assertRedirect();

        $student->refresh();
        $this->assertSame('student', $student->default_portal);
    }

    public function test_checkout_does_not_leak_unlinked_user_via_kid_id_parameter(): void
    {
        $admin = User::factory()->create([
            'name' => 'Secret Admin Name',
            'role' => 'admin',
            'is_kid' => false,
        ]);

        $otherKid = User::factory()->create([
            'name' => 'Unlinked Child',
            'role' => 'student',
            'is_kid' => true,
            'school' => 'Private Academy',
        ]);

        $course = Course::create([
            'title' => 'Python Mastery',
            'code' => 'PY-201',
            'overview' => 'Learn Python',
            'fees' => '1500',
            'is_active' => true,
            'is_for_kids' => false,
        ]);

        // Unauthenticated attacker probing kid_id
        $guestResponse = $this->get(route('checkout.show', ['course' => $course->id, 'kid_id' => $admin->id]));
        $guestResponse->assertOk();
        $guestResponse->assertDontSee('Secret Admin Name');

        $guestKidResponse = $this->get(route('checkout.show', ['course' => $course->id, 'kid_id' => $otherKid->id]));
        $guestKidResponse->assertOk();
        $guestKidResponse->assertDontSee('Unlinked Child');
    }

    public function test_document_response_forces_attachment_and_nosniff_for_executable_or_html_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('test-xss.svg', '<svg onload="alert(1)"></svg>');

        $response = DocumentResponse::serve(
            rawPath: 'test-xss.svg',
            downloadName: 'test-xss.svg',
            isDownload: false,
            preferredDisk: 'public'
        );

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('application/octet-stream', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('attachment;', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_broadpay_webhook_rejects_missing_secret_in_production(): void
    {
        config(['broadpay.webhook_secret' => '']);
        $gateway = new BroadPayGateway();

        $this->app['env'] = 'production';
        try {
            $this->assertFalse($gateway->verifyWebhookSignature('{"status":"completed"}', 'fake-sig'));
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}

