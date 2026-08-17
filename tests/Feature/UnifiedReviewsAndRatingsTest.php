<?php

namespace Tests\Feature;

use App\Livewire\Reviews\ReviewList;
use App\Livewire\Reviews\SubmitReviewModal;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\TestCase;

class UnifiedReviewsAndRatingsTest extends TestCase
{
    use RefreshDatabase;

    private function createCourse(array $attributes = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Fullstack Web Development',
            'code' => 'FWD-' . rand(100, 999),
            'is_active' => true,
        ], $attributes));
    }

    public function test_course_reviews_update_cached_average_rating_and_count(): void
    {
        $course = $this->createCourse();

        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        // Student 1 leaves 5-star review
        Review::create([
            'user_id' => $student1->id,
            'reviewable_type' => Course::class,
            'reviewable_id' => $course->id,
            'rating' => 5,
            'title' => 'Excellent course',
            'comment' => 'Learned a lot of practical skills!',
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $course->refresh();
        $this->assertEquals(5.00, (float) $course->average_rating);
        $this->assertEquals(1, $course->review_count);

        // Student 2 leaves 3-star review
        $review2 = Review::create([
            'user_id' => $student2->id,
            'reviewable_type' => Course::class,
            'reviewable_id' => $course->id,
            'rating' => 3,
            'title' => 'Average content',
            'comment' => 'Could be improved with more exercises.',
            'is_approved' => true,
            'is_verified' => true,
        ]);

        $course->refresh();
        $this->assertEquals(4.00, (float) $course->average_rating);
        $this->assertEquals(2, $course->review_count);

        // Update review 2 to 4-star
        $review2->update(['rating' => 4]);
        $course->refresh();
        $this->assertEquals(4.50, (float) $course->average_rating);
        $this->assertEquals(2, $course->review_count);

        // Delete review 2
        $review2->delete();
        $course->refresh();
        $this->assertEquals(5.00, (float) $course->average_rating);
        $this->assertEquals(1, $course->review_count);
    }

    public function test_instructor_reviews_update_cached_instructor_rating_and_count(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        Review::create([
            'user_id' => $student1->id,
            'reviewable_type' => User::class,
            'reviewable_id' => $instructor->id,
            'rating' => 5,
            'title' => 'Great mentor',
            'comment' => 'Very supportive and attentive instructor.',
            'is_approved' => true,
        ]);

        $instructor->refresh();
        $this->assertEquals(5.00, (float) $instructor->instructor_rating);
        $this->assertEquals(1, $instructor->instructor_review_count);

        Review::create([
            'user_id' => $student2->id,
            'reviewable_type' => User::class,
            'reviewable_id' => $instructor->id,
            'rating' => 4,
            'title' => 'Helpful teacher',
            'comment' => 'Provided good guidance on assignments.',
            'is_approved' => true,
        ]);

        $instructor->refresh();
        $this->assertEquals(4.50, (float) $instructor->instructor_rating);
        $this->assertEquals(2, $instructor->instructor_review_count);
    }

    public function test_platform_reviews_and_model_scopes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = $this->createCourse();

        $platformReview = Review::create([
            'user_id' => $student->id,
            'reviewable_type' => null,
            'reviewable_id' => null,
            'rating' => 5,
            'title' => 'Loving thinker_HUB',
            'comment' => 'The best tech community learning platform!',
            'is_approved' => true,
        ]);

        $courseReview = Review::create([
            'user_id' => $student->id,
            'reviewable_type' => Course::class,
            'reviewable_id' => $course->id,
            'rating' => 4,
            'title' => 'Good course',
            'comment' => 'Nice structure and pace.',
            'is_approved' => true,
        ]);

        $unapprovedReview = Review::create([
            'user_id' => $student->id,
            'reviewable_type' => null,
            'reviewable_id' => null,
            'rating' => 1,
            'title' => 'Spam',
            'comment' => 'Unapproved spam content',
            'is_approved' => false,
        ]);

        $this->assertEquals(2, Review::approved()->count());
        $this->assertEquals(1, Review::approved()->platformOnly()->count());
        $this->assertEquals($platformReview->id, Review::approved()->platformOnly()->first()->id);
        $this->assertEquals(1, Review::forModel($course)->count());
        $this->assertEquals($courseReview->id, Review::forModel($course)->first()->id);
    }

    public function test_rating_stars_blade_component_renders_correctly(): void
    {
        $html = Blade::render('<x-rating-stars :rating="4.8" :count="124" size="sm" />');

        $this->assertStringContainsString('4.8', $html);
        $this->assertStringContainsString('(124)', $html);
        $this->assertStringContainsString('text-amber-400', $html);
    }

    public function test_submit_review_modal_creates_review_and_awards_gamification_points(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $course = $this->createCourse();

        // Enroll student in course
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($student)
            ->test(SubmitReviewModal::class)
            ->call('openModal', 'course', $course->id, $course->title)
            ->assertSet('isOpen', true)
            ->assertSet('targetType', 'course')
            ->assertSet('targetId', $course->id)
            ->set('rating', 5)
            ->set('title', 'Masterclass Experience')
            ->set('comment', 'This course changed my career trajectory! Outstanding practical assignments.')
            ->call('submitReview')
            ->assertSet('isOpen', false)
            ->assertDispatched('reviewSubmitted');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $student->id,
            'reviewable_type' => Course::class,
            'reviewable_id' => $course->id,
            'rating' => 5,
            'title' => 'Masterclass Experience',
            'is_verified' => true,
        ]);

        $course->refresh();
        $this->assertEquals(5.00, (float) $course->average_rating);
        $this->assertEquals(1, $course->review_count);

        // Check XP & Thinker Coins award
        $student->refresh();
        $this->assertGreaterThanOrEqual(10, $student->lifetime_xp);
        $this->assertGreaterThanOrEqual(3, $student->spendable_coins);
        $this->assertTrue(
            XpTransaction::query()
                ->where('user_id', $student->id)
                ->where('activity_type', 'course_rating')
                ->exists()
        );
    }

    public function test_review_list_component_renders_aggregated_breakdown_and_filters(): void
    {
        $course = $this->createCourse();
        $student1 = User::factory()->create(['role' => 'student', 'name' => 'Alice Walker']);
        $student2 = User::factory()->create(['role' => 'student', 'name' => 'Bob Builder']);

        Review::create([
            'user_id' => $student1->id,
            'reviewable_type' => Course::class,
            'reviewable_id' => $course->id,
            'rating' => 5,
            'title' => 'Amazing Course',
            'comment' => 'Clear explanations and top-notch materials.',
            'is_approved' => true,
        ]);

        Review::create([
            'user_id' => $student2->id,
            'reviewable_type' => Course::class,
            'reviewable_id' => $course->id,
            'rating' => 4,
            'title' => 'Great Pace',
            'comment' => 'Enjoyed the pace and the exercises.',
            'is_approved' => true,
        ]);

        Livewire::test(ReviewList::class, [
            'targetType' => 'course',
            'targetId' => $course->id,
            'targetTitle' => $course->title,
        ])
            ->assertSee('4.5')
            ->assertSee('Amazing Course')
            ->assertSee('Alice Walker')
            ->assertSee('Great Pace')
            ->assertSee('Bob Builder')
            ->call('setFilterRating', 5)
            ->assertSee('Amazing Course')
            ->assertDontSee('Great Pace');
    }
}
