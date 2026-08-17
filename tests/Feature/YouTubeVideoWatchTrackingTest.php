<?php

namespace Tests\Feature;

use App\Livewire\VideoPlayer;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class YouTubeVideoWatchTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_video_player_mounts_with_lesson_details(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'title' => 'Web Development',
            'code' => 'WD101',
            'is_active' => true,
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Introduction to Laravel & Livewire',
            'description' => 'Learn the fundamentals of reactive web applications.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_seconds' => 300,
            'is_published' => true,
        ]);

        $this->actingAs($student);

        Livewire::test(VideoPlayer::class, ['lesson' => $lesson])
            ->assertSet('lesson.id', $lesson->id)
            ->assertSet('youtubeId', 'dQw4w9WgXcQ')
            ->assertSet('pointsEarned', false)
            ->assertSee('Introduction to Laravel &amp; Livewire', false)
            ->assertSee('Watch 85% for +10 XP / +5 TC', false);
    }

    public function test_student_earns_points_when_watching_at_least_85_percent_of_video(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $lesson = Lesson::create([
            'title' => 'Mastering Alpine.js',
            'youtube_url' => 'https://youtu.be/kJQP7kiw5Fk',
            'duration_seconds' => 100,
        ]);

        $this->actingAs($student);

        Livewire::test(VideoPlayer::class, ['lesson' => $lesson])
            ->call('awardVideoCompletionPoints', [
                'actualSecondsWatched' => 85,
                'duration' => 100,
                'currentTime' => 86,
            ])
            ->assertSet('pointsEarned', true)
            ->assertDispatched('points-awarded');

        $student->refresh();
        $this->assertEquals(10, $student->lifetime_xp);
        $this->assertEquals(3, $student->spendable_coins);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'activity_type' => 'lesson_video_completed',
            'subject_type' => Lesson::class,
            'subject_id' => $lesson->id,
            'amount_xp' => 10,
            'amount_coins' => 3,
        ]);
    }

    public function test_anti_scrubbing_prevents_points_when_actual_seconds_watched_is_below_threshold(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $lesson = Lesson::create([
            'title' => 'Anti-Scrubbing Security Test',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_seconds' => 600,
        ]);

        $this->actingAs($student);

        // Student scrubbed directly to 550s (91%) but only played for 10 seconds!
        Livewire::test(VideoPlayer::class, ['lesson' => $lesson])
            ->call('awardVideoCompletionPoints', [
                'actualSecondsWatched' => 10,
                'duration' => 600,
                'currentTime' => 550,
            ])
            ->assertSet('pointsEarned', false)
            ->assertNotDispatched('points-awarded');

        $student->refresh();
        $this->assertEquals(0, $student->lifetime_xp);
        $this->assertEquals(0, $student->spendable_coins);
        $this->assertDatabaseCount('xp_transactions', 0);
    }

    public function test_anti_scrubbing_prevents_points_when_playback_position_is_lagging(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $lesson = Lesson::create([
            'title' => 'Playback Position Lagging Test',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_seconds' => 200,
        ]);

        $this->actingAs($student);

        // Actual seconds watched is high, but playback head is still at 20s
        Livewire::test(VideoPlayer::class, ['lesson' => $lesson])
            ->call('awardVideoCompletionPoints', [
                'actualSecondsWatched' => 180,
                'duration' => 200,
                'currentTime' => 20,
            ])
            ->assertSet('pointsEarned', false);

        $student->refresh();
        $this->assertEquals(0, $student->lifetime_xp);
        $this->assertDatabaseCount('xp_transactions', 0);
    }

    public function test_duplicate_claims_for_same_lesson_are_prevented(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $lesson = Lesson::create([
            'title' => 'Idempotency Test Lesson',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_seconds' => 100,
        ]);

        $this->actingAs($student);

        $component = Livewire::test(VideoPlayer::class, ['lesson' => $lesson]);

        // First Claim
        $component->call('awardVideoCompletionPoints', [
            'actualSecondsWatched' => 90,
            'duration' => 100,
            'currentTime' => 90,
        ])->assertSet('pointsEarned', true);

        $student->refresh();
        $this->assertEquals(10, $student->lifetime_xp);
        $this->assertEquals(3, $student->spendable_coins);

        // Second Claim Attempt
        $component->call('awardVideoCompletionPoints', [
            'actualSecondsWatched' => 100,
            'duration' => 100,
            'currentTime' => 100,
        ]);

        // Should still only have 10 XP and 3 TC
        $student->refresh();
        $this->assertEquals(10, $student->lifetime_xp);
        $this->assertEquals(3, $student->spendable_coins);
        $this->assertDatabaseCount('xp_transactions', 1);
    }

    public function test_video_player_recognizes_previously_completed_lesson_on_mount(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create([
            'title' => 'Previously Watched Lesson',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration_seconds' => 120,
        ]);

        // Pre-create XP transaction
        XpTransaction::create([
            'user_id' => $student->id,
            'amount_xp' => 10,
            'amount_coins' => 5,
            'activity_type' => 'lesson_video_completed',
            'subject_type' => Lesson::class,
            'subject_id' => $lesson->id,
            'points' => 10,
            'source' => 'lesson_video_completed',
            'source_id' => (string) $lesson->id,
            'description' => 'Completed video lesson',
        ]);

        $this->actingAs($student);

        Livewire::test(VideoPlayer::class, ['lesson' => $lesson])
            ->assertSet('pointsEarned', true)
            ->assertSee('Points Claimed (+10 XP / +5 TC)', false);
    }
}
