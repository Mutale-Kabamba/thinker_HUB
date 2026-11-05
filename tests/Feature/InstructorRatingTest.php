<?php

namespace Tests\Feature;

use App\Livewire\Reviews\CreateReviewPage;
use App\Livewire\Reviews\SubmitReviewModal;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstructorRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_rate_instructor_via_create_review_page(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Tutor Chanda',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Data Analytics Mastery',
            'code' => 'DA-101',
            'overview' => 'Data Analytics course',
            'fees' => '1500',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        Livewire::actingAs($student)
            ->test(CreateReviewPage::class, [
                'targetType' => 'instructor',
                'targetId' => $instructor->id,
            ])
            ->set('rating', 5)
            ->set('title', 'Fantastic Instructor')
            ->set('comment', 'Explained concepts very clearly.')
            ->call('submitReview')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $review = Review::where('user_id', $student->id)
            ->where('reviewable_type', User::class)
            ->where('reviewable_id', $instructor->id)
            ->first();

        $this->assertNotNull($review);
        $this->assertSame(5, $review->rating);
        $this->assertTrue($review->is_verified);

        $instructor->refresh();
        $this->assertEquals(5.00, (float) $instructor->instructor_rating);
        $this->assertSame(1, $instructor->instructor_review_count);
    }

    public function test_student_can_rate_instructor_via_submit_review_modal(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Tutor Mutale',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
            'is_active' => true,
        ]);

        Livewire::actingAs($student)
            ->test(SubmitReviewModal::class)
            ->call('openModal', 'instructor', $instructor->id, $instructor->name)
            ->set('rating', 4)
            ->set('comment', 'Helpful mentorship sessions.')
            ->call('submitReview')
            ->assertHasNoErrors()
            ->assertSet('isOpen', false);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $student->id,
            'reviewable_type' => User::class,
            'reviewable_id' => $instructor->id,
            'rating' => 4,
        ]);
    }

    public function test_instructor_rating_is_displayed_on_network_cards_and_profile_page(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Mutale Kabamba',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        Review::create([
            'user_id' => $student->id,
            'reviewable_type' => User::class,
            'reviewable_id' => $instructor->id,
            'rating' => 5,
            'comment' => 'Great mentor!',
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $networkResponse = $this->get('/network');
        $networkResponse->assertOk();
        $networkResponse->assertSee('Mutale Kabamba');
        $networkResponse->assertSee('5.0');
        $networkResponse->assertSee('(1)');

        $profileResponse = $this->get(route('landing.instructors.show', ['instructor' => $instructor->id]));
        $profileResponse->assertOk();
        $profileResponse->assertSee('5.0');
        $profileResponse->assertSee('(1)');
    }
}

