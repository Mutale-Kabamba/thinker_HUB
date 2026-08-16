<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\User;
use App\Notifications\AssessmentAssignedNotification;
use App\Notifications\AssignmentAssignedNotification;
use App\Notifications\MaterialPublishedNotification;
use App\Notifications\QuizPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentEmailNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_assignment_sends_email_and_database_notification_to_enrolled_students(): void
    {
        Notification::fake();

        $instructor = User::factory()->create(['role' => 'instructor', 'name' => 'Prof. Oak']);
        $student1 = User::factory()->create(['role' => 'student', 'name' => 'Ash Ketchum', 'email' => 'ash@example.com']);
        $student2 = User::factory()->create(['role' => 'student', 'name' => 'Misty Waterflower', 'email' => 'misty@example.com']);
        $otherStudent = User::factory()->create(['role' => 'student', 'name' => 'Gary Oak', 'email' => 'gary@example.com']);

        $course = Course::create([
            'title' => 'Pokemon Science',
            'code' => 'PK101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        Enrollment::create(['user_id' => $student1->id, 'course_id' => $course->id]);
        Enrollment::create(['user_id' => $student2->id, 'course_id' => $course->id]);

        $this->actingAs($instructor);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Lab Report 1: Electric Types',
            'description' => 'Write a comprehensive report on Pikachu physiology.',
            'due_date' => now()->addDays(7),
        ]);

        Notification::assertSentTo([$student1, $student2], AssignmentAssignedNotification::class, function ($notification) use ($assignment) {
            $mail = $notification->toMail(new User());
            return str_contains($mail->subject, 'Lab Report 1: Electric Types');
        });

        Notification::assertNotSentTo($otherStudent, AssignmentAssignedNotification::class);
    }

    public function test_new_assessment_sends_email_and_database_notification_to_enrolled_students(): void
    {
        Notification::fake();

        $instructor = User::factory()->create(['role' => 'instructor', 'name' => 'Prof. Oak']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Brock Slate', 'email' => 'brock@example.com']);
        $otherStudent = User::factory()->create(['role' => 'student', 'email' => 'unrelated@example.com']);

        $course = Course::create([
            'title' => 'Rock Types 101',
            'code' => 'RCK101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($instructor);

        $assessment = Assessment::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'name' => 'Midterm Assessment Exam',
            'description' => 'Geodude and Onix tactical review.',
            'due_date' => now()->addDays(5),
        ]);

        Notification::assertSentTo($student, AssessmentAssignedNotification::class, function ($notification) {
            $mail = $notification->toMail(new User());
            return str_contains($mail->subject, 'Midterm Assessment Exam');
        });

        Notification::assertNotSentTo($otherStudent, AssessmentAssignedNotification::class);
    }

    public function test_new_quiz_sends_email_notification_to_enrolled_students(): void
    {
        Notification::fake();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'email' => 'learner@example.com']);

        $course = Course::create([
            'title' => 'JavaScript Advanced',
            'code' => 'JS201',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($instructor);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Asynchronous JS Quiz',
            'description' => 'Promises and Async/Await',
            'time_limit_minutes' => 20,
            'pass_percentage' => 75,
            'is_active' => true,
        ]);

        Notification::assertSentTo($student, QuizPublishedNotification::class, function ($notification) {
            $mail = $notification->toMail(new User());
            return str_contains($mail->subject, 'Asynchronous JS Quiz');
        });
    }

    public function test_new_learning_material_sends_email_notification_to_enrolled_students(): void
    {
        Notification::fake();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'email' => 'student@example.com']);
        $otherStudent = User::factory()->create(['role' => 'student', 'email' => 'other@example.com']);

        $course = Course::create([
            'title' => 'Cloud Architecture',
            'code' => 'CLD301',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($instructor);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Module 3 Architecture Slides',
            'category' => 'Lecture Slides',
            'description' => 'Download the PDF slides for this week.',
            'scope' => 'cohort',
        ]);

        Notification::assertSentTo($student, MaterialPublishedNotification::class, function ($notification) {
            $mail = $notification->toMail(new User());
            return str_contains($mail->subject, 'Module 3 Architecture Slides');
        });

        Notification::assertNotSentTo($otherStudent, MaterialPublishedNotification::class);
    }
}
