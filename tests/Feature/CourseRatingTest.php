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

    public function test_locked_course_hides_enroll_and_pay_and_shows_locked_badge(): void
    {
        $lockedCourse = Course::query()->create([
            'title' => 'Advanced Robotics',
            'code' => 'ROB-501',
            'description' => 'Restricted track',
            'is_active' => true,
            'is_open_enrollment' => false,
        ]);

        $openCourse = Course::query()->create([
            'title' => 'Web Design Basics',
            'code' => 'WEB-101',
            'description' => 'Public track',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        // 1. Catalog Page: Locked course should not show "Enroll & Pay"
        $catalogResponse = $this->get(route('landing.courses'));
        $catalogResponse->assertOk();
        $catalogResponse->assertSee('Locked');
        $catalogResponse->assertSee('Enroll &amp; Pay', false);

        // 2. Course Details Page for locked course: should not show "Enroll & Pay (Instant Access)"
        $lockedDetailResponse = $this->get(route('landing.courses.show', ['course' => $lockedCourse->id, 'slug' => 'advanced-robotics']));
        $lockedDetailResponse->assertOk();
        $lockedDetailResponse->assertSee('Enrollment Locked');
        $lockedDetailResponse->assertDontSee('Enroll &amp; Pay (Instant Access)', false);

        // 3. Course Details Page for open course: should show "Enroll & Pay"
        $openDetailResponse = $this->get(route('landing.courses.show', ['course' => $openCourse->id, 'slug' => 'web-design-basics']));
        $openDetailResponse->assertOk();
        $openDetailResponse->assertSee('Enroll &amp; Pay (Instant Access)', false);

        // 4. Directly navigating to checkout for locked course redirects back
        $checkoutResponse = $this->get(route('checkout.show', $lockedCourse));
        $checkoutResponse->assertRedirect();
        $checkoutResponse->assertSessionHas('error');
    }

    public function test_course_page_differentiates_ratings_and_reviews_with_sliding_carousel(): void
    {
        $student = User::factory()->create([
            'name' => 'Alice Mutale',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = Course::query()->create([
            'title' => 'Machine Learning',
            'code' => 'ML-301',
            'description' => 'Deep learning and NLP',
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
            'rating' => 5,
            'review' => 'Outstanding course with real-world machine learning hands-on projects!',
        ]);

        $response = $this->get(route('landing.courses.show', ['course' => $course->id, 'slug' => 'machine-learning']));
        $response->assertOk();

        // Quantitative Ratings assertions (User ratings breakdown)
        $response->assertSee('User ratings');
        $response->assertSee('5 out of 5');
        $response->assertSee('5 star');
        $response->assertSee('100%');
        $response->assertSee('1 user rating');

        // Qualitative Reviews & 4s Auto-slider assertions
        $response->assertSee('Student Reviews');
        $response->assertSee('4000');
        $response->assertSee('Alice Mutale');
        $response->assertSee('Outstanding course with real-world machine learning hands-on projects!');

        // Home Page Real Feedback section
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('Real Feedback');
        $homeResponse->assertSee('Student Reviews &amp; Ratings', false);
        $homeResponse->assertSee('User ratings');

        // Courses Catalog Page Reviews section
        $coursesResponse = $this->get(route('landing.courses'));
        $coursesResponse->assertOk();
        $coursesResponse->assertSee('Student Reviews &amp; Ratings', false);
        $coursesResponse->assertSee('User ratings');
    }
}

