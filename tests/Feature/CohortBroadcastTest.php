<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\Broadcasts;
use App\Mail\CohortBroadcast;
use App\Models\Broadcast;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CohortBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_sees_assigned_courses_and_enrolled_student_count(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student1 = User::factory()->create(['role' => 'student', 'email' => 'student1@example.com']);
        $student2 = User::factory()->create(['role' => 'student', 'email' => 'student2@example.com']);

        $course = Course::query()->create([
            'title' => 'Full Stack Web Development',
            'code' => 'FSW101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        Enrollment::create(['user_id' => $student1->id, 'course_id' => $course->id]);
        Enrollment::create(['user_id' => $student2->id, 'course_id' => $course->id]);

        $this->actingAs($instructor);

        Livewire::test(Broadcasts::class)
            ->assertSee('Full Stack Web Development (FSW101)')
            ->set('courseId', (string) $course->id)
            ->assertSee('2 enrolled students will receive this broadcast');
    }

    public function test_instructor_can_send_cohort_broadcast_to_enrolled_students(): void
    {
        Mail::fake();

        $instructor = User::factory()->create(['role' => 'instructor', 'name' => 'Jane Instructor']);
        $student1 = User::factory()->create(['role' => 'student', 'name' => 'Alice Student', 'email' => 'alice@example.com']);
        $student2 = User::factory()->create(['role' => 'student', 'name' => 'Bob Student', 'email' => 'bob@example.com']);

        $course = Course::query()->create([
            'title' => 'Mobile App Mastery',
            'code' => 'MAM101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        Enrollment::create(['user_id' => $student1->id, 'course_id' => $course->id]);
        Enrollment::create(['user_id' => $student2->id, 'course_id' => $course->id]);

        $this->actingAs($instructor);

        Livewire::test(Broadcasts::class)
            ->set('courseId', (string) $course->id)
            ->set('subject', 'Live Q&A Tomorrow at 10 AM')
            ->set('message', 'Please be on time and bring your questions!')
            ->call('send')
            ->assertNotified('Broadcast sent to 2 students')
            ->assertSet('subject', '')
            ->assertSet('message', '');

        // Verify Mailable sent to both students
        Mail::assertSent(CohortBroadcast::class, 2);
        Mail::assertSent(CohortBroadcast::class, fn (CohortBroadcast $mail) => $mail->hasTo('alice@example.com') && $mail->subjectLine === 'Live Q&A Tomorrow at 10 AM');
        Mail::assertSent(CohortBroadcast::class, fn (CohortBroadcast $mail) => $mail->hasTo('bob@example.com') && $mail->subjectLine === 'Live Q&A Tomorrow at 10 AM');

        // Verify in-app notifications created in database for students
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $student1->id,
            'notifiable_type' => User::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $student2->id,
            'notifiable_type' => User::class,
        ]);

        // Verify Broadcast audit record created
        $broadcast = Broadcast::query()->where('course_id', $course->id)->first();
        $this->assertNotNull($broadcast);
        $this->assertSame('Live Q&A Tomorrow at 10 AM', $broadcast->subject);
        $this->assertSame('Please be on time and bring your questions!', $broadcast->body);
        $this->assertSame(2, $broadcast->recipients_count);
        $this->assertSame(0, $broadcast->failed_count);
        $this->assertNotNull($broadcast->sent_at);
    }

    public function test_broadcast_validation_and_unauthorized_course_protection(): void
    {
        Mail::fake();

        $instructor1 = User::factory()->create(['role' => 'instructor']);
        $instructor2 = User::factory()->create(['role' => 'instructor']);

        $course1 = Course::query()->create(['title' => 'Course One', 'code' => 'C101', 'is_active' => true]);
        $course1->instructors()->attach($instructor1->id);

        $course2 = Course::query()->create(['title' => 'Course Two', 'code' => 'C102', 'is_active' => true]);
        $course2->instructors()->attach($instructor2->id);

        $this->actingAs($instructor1);

        // Blank input validation
        Livewire::test(Broadcasts::class)
            ->set('courseId', '')
            ->set('subject', '')
            ->set('message', '')
            ->call('send')
            ->assertNotified('Please choose a course and enter a subject and message.');

        // Attempting to send to another instructor's course
        Livewire::test(Broadcasts::class)
            ->set('courseId', (string) $course2->id)
            ->set('subject', 'Unauthorized')
            ->set('message', 'Should not send')
            ->call('send')
            ->assertNotified('You can only broadcast to your own active courses.');

        Mail::assertNothingSent();
        $this->assertSame(0, Broadcast::query()->count());
    }

    public function test_course_with_no_enrolled_students_warns_instructor(): void
    {
        Mail::fake();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::query()->create(['title' => 'Empty Course', 'code' => 'EMP101', 'is_active' => true]);
        $course->instructors()->attach($instructor->id);

        $this->actingAs($instructor);

        Livewire::test(Broadcasts::class)
            ->set('courseId', (string) $course->id)
            ->set('subject', 'Announcement')
            ->set('message', 'Hello anyone?')
            ->call('send')
            ->assertNotified('No enrolled students with email addresses found in this course.');

        Mail::assertNothingSent();
    }

    public function test_admin_can_broadcast_to_any_active_course(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'email' => 'student@example.com']);
        $course = Course::query()->create(['title' => 'General Course', 'code' => 'GEN101', 'is_active' => true]);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($admin);

        Livewire::test(Broadcasts::class)
            ->set('courseId', (string) $course->id)
            ->set('subject', 'Admin Announcement')
            ->set('message', 'Platform-wide update.')
            ->call('send')
            ->assertNotified('Broadcast sent to 1 student');

        Mail::assertSent(CohortBroadcast::class, 1);
    }
}
