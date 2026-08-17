<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\StudentResults;
use App\Models\Badge;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstructorManualGamificationAwardTest extends TestCase
{
    use RefreshDatabase;

    public function test_gamification_service_awards_manual_instructor_xp_and_coins(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $course = Course::create([
            'title' => 'Web Development Bootcamp',
            'code' => 'WEB-101',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);

        $service = app(GamificationService::class);
        $result = $service->awardManualInstructorReward(
            instructor: $instructor,
            student: $student,
            course: $course,
            activityName: 'Outstanding Presentation',
            xp: 75,
            coins: 25,
            badgeKeyOrId: null,
            awardBadgeXp: false,
            note: 'Delivered an exceptional neural network demo'
        );

        $this->assertTrue($result['success']);
        $this->assertSame(75, $result['xp']);
        $this->assertSame(25, $result['coins']);

        $student->refresh();
        $this->assertSame(75, $student->lifetime_xp);
        $this->assertSame(25, $student->spendable_coins);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'amount_xp' => 75,
            'amount_coins' => 25,
            'activity_type' => 'instructor_award',
            'source' => 'instructor_award',
        ]);
    }

    public function test_gamification_service_defaults_coins_to_30_percent_of_xp(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $service = app(GamificationService::class);
        // 100 XP with null coins -> 30 TC
        $result = $service->awardManualInstructorReward(
            instructor: $instructor,
            student: $student,
            course: null,
            activityName: 'Peer Mentoring',
            xp: 100,
            coins: null
        );

        $this->assertTrue($result['success']);
        $this->assertSame(100, $result['xp']);
        $this->assertSame(30, $result['coins']);

        $student->refresh();
        $this->assertSame(100, $student->lifetime_xp);
        $this->assertSame(30, $student->spendable_coins);
    }

    public function test_gamification_service_awards_badge_and_bonus_xp_for_off_platform_achievement(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $badge = Badge::query()->firstOrCreate(
            ['key' => 'outstanding_presentation'],
            [
                'name' => 'Presentation Star',
                'description' => 'Delivered an outstanding presentation.',
                'icon' => '🎤',
                'xp_reward' => 50,
            ]
        );

        $service = app(GamificationService::class);
        $result = $service->awardManualInstructorReward(
            instructor: $instructor,
            student: $student,
            course: null,
            activityName: 'Presentation Star',
            xp: 50,
            coins: 15,
            badgeKeyOrId: $badge->id,
            awardBadgeXp: true,
            note: 'Captivating presentation in classroom'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('Presentation Star', $result['badge']);

        $student->refresh();
        // 50 (manual) + 50 (badge bonus) = 100 XP
        $this->assertSame(100, $student->lifetime_xp);

        $this->assertDatabaseHas('user_badge', [
            'user_id' => $student->id,
            'badge_id' => $badge->id,
        ]);
    }

    public function test_student_results_page_instructor_can_award_points_and_badges_via_livewire(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 10,
            'spendable_coins' => 5,
        ]);

        $course = Course::create([
            'title' => 'Python for AI',
            'code' => 'PY-202',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($instructor);

        Livewire::test(StudentResults::class)
            ->call('openAwardModal', $student->id)
            ->assertSet('showAwardModal', true)
            ->assertSet('awardStudentId', $student->id)
            ->set('awardActivityType', 'custom')
            ->set('awardCustomActivity', 'Hackathon 1st Place Winner')
            ->set('awardXp', 120)
            ->set('awardCoins', 36)
            ->set('awardNote', 'Built an innovative fullstack prototype')
            ->call('submitAward')
            ->assertSet('showAwardModal', false);

        $student->refresh();
        $this->assertSame(130, $student->lifetime_xp); // 10 + 120
        $this->assertSame(41, $student->spendable_coins); // 5 + 36

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'amount_xp' => 120,
            'amount_coins' => 36,
            'activity_type' => 'instructor_award',
        ]);
    }

    public function test_award_manual_instructor_reward_rejects_non_students(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $admin = User::factory()->create(['role' => 'admin']);

        $service = app(GamificationService::class);
        $result = $service->awardManualInstructorReward(
            instructor: $instructor,
            student: $admin,
            course: null,
            activityName: 'Presentation',
            xp: 50
        );

        $this->assertFalse($result['success']);
        $this->assertSame('Recipient is not a student.', $result['message']);
    }
}
