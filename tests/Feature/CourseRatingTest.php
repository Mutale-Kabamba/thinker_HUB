<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_can_create_course_rating(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = Course::query()->create([
            'title' => 'Data Foundations',
            'code' => 'DF-101',
            'description' => 'Intro course',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($student)
            ->post(route('course.rate', $course->id), [
                'rating' => 5,
                'review' => 'Very practical and clear.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('course_ratings', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 5,
            'review' => 'Very practical and clear.',
        ]);
    }

    public function test_enrolled_student_can_update_existing_rating(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WD-101',
            'description' => 'Web intro',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        CourseRating::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 3,
            'review' => 'Decent start.',
        ]);

        $this->actingAs($student)
            ->post(route('course.rate', $course->id), [
                'rating' => 4,
                'review' => "Great update with examples.\nNow much better.",
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('course_ratings', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 4,
            'review' => "Great update with examples.\nNow much better.",
        ]);

        $this->assertSame(1, CourseRating::query()->where('course_id', $course->id)->where('user_id', $student->id)->count());
    }

    public function test_non_enrolled_student_cannot_rate_course(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = Course::query()->create([
            'title' => 'AI Basics',
            'code' => 'AI-101',
            'description' => 'AI intro',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        $this->actingAs($student)
            ->post(route('course.rate', $course->id), [
                'rating' => 5,
                'review' => 'Excellent.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('course_ratings', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
    }
}
