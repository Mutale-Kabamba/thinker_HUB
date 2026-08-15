<?php

namespace Tests\Feature;

use App\Livewire\ClaimHub\Storefront;
use App\Models\ClaimItem;
use App\Models\ClaimRequest;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ClaimHubAndEconomyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        // Create global rule set for economy & anti-gaming tests
        \App\Models\CourseGamificationRule::create([
            'course_id' => null,
            'name' => 'Global Platform Rules',
            'rules' => \App\Models\CourseGamificationRule::getDefaultMatrix(),
            'is_active' => true,
        ]);
    }


    public function test_rank_multipliers_calculate_correctly_based_on_lifetime_xp(): void
    {
        $service = app(GamificationService::class);

        $this->assertSame(['rank_name' => 'Novice', 'multiplier' => 1.0], $service->calculateUserRank(0));
        $this->assertSame(['rank_name' => 'Novice', 'multiplier' => 1.0], $service->calculateUserRank(499));
        $this->assertSame(['rank_name' => 'Apprentice', 'multiplier' => 1.05], $service->calculateUserRank(500));
        $this->assertSame(['rank_name' => 'Apprentice', 'multiplier' => 1.05], $service->calculateUserRank(1499));
        $this->assertSame(['rank_name' => 'Scholar', 'multiplier' => 1.10], $service->calculateUserRank(1500));
        $this->assertSame(['rank_name' => 'Scholar', 'multiplier' => 1.10], $service->calculateUserRank(3499));
        $this->assertSame(['rank_name' => 'Master', 'multiplier' => 1.15], $service->calculateUserRank(3500));
        $this->assertSame(['rank_name' => 'Master', 'multiplier' => 1.15], $service->calculateUserRank(7499));
        $this->assertSame(['rank_name' => 'Grandmaster', 'multiplier' => 1.25], $service->calculateUserRank(7500));
        $this->assertSame(['rank_name' => 'Grandmaster', 'multiplier' => 1.25], $service->calculateUserRank(12000));
    }

    public function test_award_points_applies_rank_multiplier_and_increments_user_balances(): void
    {
        $service = app(GamificationService::class);
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 1600, // Scholar -> 1.10x multiplier
            'spendable_coins' => 50,
        ]);

        $result = $service->awardPoints($student, 'test_activity', null, 100, 20, 'Test reward');

        $this->assertTrue($result);
        $student->refresh();

        // 1600 + 100 = 1700 XP
        $this->assertSame(1700, $student->lifetime_xp);
        // Base 20 * 1.10 = 22 TC -> 50 + 22 = 72 TC
        $this->assertSame(72, $student->spendable_coins);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'activity_type' => 'test_activity',
            'amount_xp' => 100,
            'amount_coins' => 22,
        ]);
    }

    public function test_daily_coin_cap_enforces_max_150_tc_per_calendar_day(): void
    {
        $service = app(GamificationService::class);
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0, // 1.0x multiplier
            'spendable_coins' => 0,
        ]);

        // Award 120 TC
        $service->awardPoints($student, 'task_1', null, 100, 120, 'Task 1');
        $student->refresh();
        $this->assertSame(120, $student->spendable_coins);

        // Attempt to award 50 more TC on the same day -> capped at remaining 30 TC
        $service->awardPoints($student, 'task_2', null, 100, 50, 'Task 2');
        $student->refresh();
        $this->assertSame(150, $student->spendable_coins);

        // Third attempt for 30 TC on the same day -> 0 TC granted because cap of 150 is reached
        $service->awardPoints($student, 'task_3', null, 50, 30, 'Task 3');
        $student->refresh();
        $this->assertSame(150, $student->spendable_coins);
        $this->assertSame(250, $student->lifetime_xp); // XP still earned!
    }

    public function test_daily_streak_increments_and_awards_milestone_bonuses(): void
    {
        $service = app(GamificationService::class);
        $student = User::factory()->create([
            'role' => 'student',
            'current_streak' => 6,
            'last_activity_at' => Carbon::yesterday(),
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $service->checkDailyStreak($student);
        $student->refresh();

        // Streak reached 7 days
        $this->assertSame(7, $student->current_streak);
        // Base login (5 XP, 2 TC) + 7-Day Milestone (50 XP, 15 TC [30%]) = 55 XP, 17 TC
        $this->assertSame(55, $student->lifetime_xp);
        $this->assertSame(17, $student->spendable_coins);

        // Streak 7 badge awarded
        $this->assertTrue($student->badges()->where('badges.key', 'streak_7')->exists());
    }

    public function test_daily_streak_resets_if_activity_was_broken(): void
    {
        $service = app(GamificationService::class);
        $student = User::factory()->create([
            'role' => 'student',
            'current_streak' => 5,
            'last_activity_at' => Carbon::now()->subDays(3), // Broken streak
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $service->checkDailyStreak($student);
        $student->refresh();

        $this->assertSame(1, $student->current_streak);
        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);
    }

    public function test_anti_gaming_quiz_retakes_give_zero_coins_and_zero_xp(): void
    {
        $service = app(GamificationService::class);
        $student = User::factory()->create(['role' => 'student', 'spendable_coins' => 0, 'lifetime_xp' => 0]);
        $course = Course::create(['title' => 'Laravel Mastery', 'code' => 'LAR101', 'is_active' => true]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Eloquent Quiz', 'is_active' => true]);

        // First passed attempt (default matrix: 25 XP, 8 TC [30%])
        $attempt1 = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'percentage' => 80,
            'passed' => true,
            'completed_at' => now()->subHours(2),
        ]);

        $service->awardQuizPassed($student, $attempt1);
        $student->refresh();

        $this->assertSame(25, $student->lifetime_xp);
        $this->assertSame(8, $student->spendable_coins);

        // Second passed attempt on the same quiz (Retake)
        $attempt2 = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'percentage' => 90,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $service->awardQuizPassed($student, $attempt2);
        $student->refresh();

        // Balances must remain unchanged (Anti-gaming: 0 TC / 0 XP on retake)
        $this->assertSame(25, $student->lifetime_xp);
        $this->assertSame(8, $student->spendable_coins);
    }

    public function test_anti_gaming_video_watched_requires_at_least_85_percent(): void
    {
        $service = app(GamificationService::class);
        $student = User::factory()->create(['role' => 'student', 'spendable_coins' => 0, 'lifetime_xp' => 0]);
        $course = Course::create(['title' => 'Web Dev', 'code' => 'WEB200', 'is_active' => true]);

        // Watch 50% -> Rejected
        $resultFail = $service->awardVideoWatched($student, $course, 50.0);
        $this->assertFalse($resultFail);
        $student->refresh();
        $this->assertSame(0, $student->spendable_coins);
        $this->assertSame(0, $student->lifetime_xp);

        // Watch 85% -> Awarded (10 XP, 3 TC [30%])
        $resultSuccess = $service->awardVideoWatched($student, $course, 85.0);
        $this->assertTrue($resultSuccess);
        $student->refresh();
        $this->assertSame(10, $student->lifetime_xp);
        $this->assertSame(3, $student->spendable_coins);
    }

    public function test_claim_hub_storefront_livewire_component_renders_and_redeems_item(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'spendable_coins' => 500,
            'lifetime_xp' => 1200,
            'whatsapp' => '+260971234567',
        ]);

        $item = ClaimItem::create([
            'title' => '2GB Data Bundle',
            'description' => 'Fast mobile data',
            'category' => ClaimItem::CATEGORY_DATA,
            'coin_cost' => 200,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($student);

        // Test Livewire component
        Livewire::test(Storefront::class)
            ->assertSee('2GB Data Bundle')
            ->assertSee('🪙 500')
            ->call('openRedeemModal', $item->id)
            ->assertSet('showModal', true)
            ->set('phoneNumber', '+260971234567')
            ->set('deliveryNotes', 'MTN Network')
            ->call('redeemItem')
            ->assertHasNoErrors();

        $student->refresh();
        $item->refresh();

        // Deducted 200 TC (500 - 200 = 300)
        $this->assertSame(300, $student->spendable_coins);
        // Stock decremented (10 -> 9)
        $this->assertSame(9, $item->stock_quantity);

        // Claim request created with status pending
        $this->assertDatabaseHas('claim_requests', [
            'user_id' => $student->id,
            'claim_item_id' => $item->id,
            'coins_spent' => 200,
            'status' => ClaimRequest::STATUS_PENDING,
            'phone_number' => '+260971234567',
            'delivery_notes' => 'MTN Network',
        ]);
    }

    public function test_claim_hub_redemption_fails_if_insufficient_coins_or_out_of_stock(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'spendable_coins' => 50,
        ]);

        $expensiveItem = ClaimItem::create([
            'title' => 'Hoodie',
            'category' => ClaimItem::CATEGORY_MERCH,
            'coin_cost' => 800,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $outOfStockItem = ClaimItem::create([
            'title' => 'Sticker',
            'category' => ClaimItem::CATEGORY_MERCH,
            'coin_cost' => 10,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($student);

        // 1. Cannot afford
        Livewire::test(Storefront::class)
            ->call('openRedeemModal', $expensiveItem->id)
            ->assertSee('You need 800 Thinker Coins')
            ->call('redeemItem', $expensiveItem->id)
            ->assertSee('Insufficient Thinker Coins');

        $this->assertSame(50, $student->fresh()->spendable_coins);

        // 2. Out of stock
        Livewire::test(Storefront::class)
            ->call('openRedeemModal', $outOfStockItem->id)
            ->assertSee('Sorry, this item is out of stock.')
            ->call('redeemItem', $outOfStockItem->id)
            ->assertSee('out of stock');
    }

    public function test_admin_rejection_refunds_coins_and_restores_stock(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'spendable_coins' => 100,
        ]);

        $item = ClaimItem::create([
            'title' => 'Branded Cap',
            'category' => ClaimItem::CATEGORY_MERCH,
            'coin_cost' => 300,
            'stock_quantity' => 4,
            'is_active' => true,
        ]);

        $claim = ClaimRequest::create([
            'user_id' => $student->id,
            'claim_item_id' => $item->id,
            'coins_spent' => 300,
            'status' => ClaimRequest::STATUS_PENDING,
            'delivery_notes' => 'Campus delivery',
        ]);

        // Simulate Admin Reject & Refund Action logic
        \Illuminate\Support\Facades\DB::transaction(function () use ($claim, $item, $student) {
            $student->increment('spendable_coins', $claim->coins_spent);
            $item->increment('stock_quantity');
            $claim->update([
                'status' => ClaimRequest::STATUS_REJECTED,
                'admin_remarks' => 'Out of requested color in warehouse.',
            ]);
        });

        $student->refresh();
        $item->refresh();
        $claim->refresh();

        $this->assertSame(400, $student->spendable_coins); // 100 + 300 refunded
        $this->assertSame(5, $item->stock_quantity); // Stock restored
        $this->assertSame(ClaimRequest::STATUS_REJECTED, $claim->status);
        $this->assertSame('Out of requested color in warehouse.', $claim->admin_remarks);
    }
}
