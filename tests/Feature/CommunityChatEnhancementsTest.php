<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Community;
use App\Models\ChatMessage;
use App\Models\ChatMessageReaction;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommunityChatEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('student'));
    }

    public function test_user_is_assigned_deterministic_chat_color_palette(): void
    {
        $student1 = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $student2 = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $palette1 = $student1->chatColorPalette();
        $palette2 = $student2->chatColorPalette();

        $this->assertArrayHasKey('accent', $palette1);
        $this->assertArrayHasKey('name_color', $palette1);
        $this->assertArrayHasKey('bg_light', $palette1);

        // Same user generates identical palette on repeated calls
        $this->assertSame($palette1, $student1->chatColorPalette());
    }

    public function test_replying_to_a_message_persists_reply_to_id_and_displays_quote(): void
    {
        $course = Course::query()->create([
            'title' => 'Biology 101',
            'code' => 'BIO-101',
            'is_active' => true,
        ]);

        $studentA = User::factory()->create(['name' => 'Alice Student', 'role' => 'student', 'is_active' => true]);
        $studentB = User::factory()->create(['name' => 'Bob Student', 'role' => 'student', 'is_active' => true]);

        $studentA->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);
        $studentB->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        $room = ChatRoom::firstOrCreate(
            ['type' => 'course', 'course_id' => $course->id],
            ['name' => 'BIO-101']
        );
        $room->members()->sync([$studentA->id, $studentB->id]);

        $firstMessage = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $studentA->id,
            'body' => 'Hello everyone! Does anyone have the notes for Chapter 3?',
        ]);

        $this->actingAs($studentB);

        Livewire::test(Community::class)
            ->call('openRoom', $room->id)
            ->call('setReplyTo', $firstMessage->id)
            ->assertSet('replyingToMessageId', $firstMessage->id)
            ->set('messageBody', 'Yes, I uploaded them to the resources section!')
            ->call('sendMessage')
            ->assertSet('replyingToMessageId', null)
            ->assertSet('messageBody', '');

        $replyMessage = ChatMessage::query()
            ->where('chat_room_id', $room->id)
            ->where('user_id', $studentB->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($replyMessage);
        $this->assertSame($firstMessage->id, $replyMessage->reply_to_id);
        $this->assertSame('Yes, I uploaded them to the resources section!', $replyMessage->body);
        $this->assertSame('Alice Student', $replyMessage->replyTo->user->name);
    }

    public function test_canceling_reply_clears_replying_state(): void
    {
        $course = Course::query()->create([
            'title' => 'Chemistry 101',
            'code' => 'CHEM-101',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $student->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        $room = ChatRoom::firstOrCreate(['type' => 'course', 'course_id' => $course->id], ['name' => 'CHEM-101']);
        $room->members()->sync([$student->id]);

        $msg = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $student->id,
            'body' => 'Original message',
        ]);

        $this->actingAs($student);

        Livewire::test(Community::class)
            ->call('openRoom', $room->id)
            ->call('setReplyTo', $msg->id)
            ->assertSet('replyingToMessageId', $msg->id)
            ->call('cancelReply')
            ->assertSet('replyingToMessageId', null);
    }

    public function test_toggling_emoji_reactions_on_messages(): void
    {
        $course = Course::query()->create([
            'title' => 'Math 101',
            'code' => 'MTH-101',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['name' => 'Charlie Student', 'role' => 'student', 'is_active' => true]);
        $student->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        $room = ChatRoom::firstOrCreate(['type' => 'course', 'course_id' => $course->id], ['name' => 'MTH-101']);
        $room->members()->sync([$student->id]);

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $student->id,
            'body' => 'Great session today!',
        ]);

        $this->actingAs($student);

        // React with thumbs up
        Livewire::test(Community::class)
            ->call('openRoom', $room->id)
            ->call('toggleReaction', $message->id, '👍');

        $this->assertDatabaseHas('chat_message_reactions', [
            'chat_message_id' => $message->id,
            'user_id' => $student->id,
            'emoji' => '👍',
        ]);

        $grouped = $message->fresh()->getGroupedReactions($student->id);
        $this->assertCount(1, $grouped);
        $this->assertSame('👍', $grouped[0]['emoji']);
        $this->assertSame(1, $grouped[0]['count']);
        $this->assertTrue($grouped[0]['reacted_by_me']);
        $this->assertContains('Charlie Student', $grouped[0]['names']);

        // Toggle thumbs up again -> removes reaction
        Livewire::test(Community::class)
            ->call('openRoom', $room->id)
            ->call('toggleReaction', $message->id, '👍');

        $this->assertDatabaseMissing('chat_message_reactions', [
            'chat_message_id' => $message->id,
            'user_id' => $student->id,
            'emoji' => '👍',
        ]);
        $this->assertCount(0, $message->fresh()->getGroupedReactions($student->id));
    }

    public function test_multiple_users_can_react_to_same_message_with_unique_constraint(): void
    {
        $course = Course::query()->create([
            'title' => 'History 101',
            'code' => 'HIS-101',
            'is_active' => true,
        ]);

        $student1 = User::factory()->create(['name' => 'Student One', 'role' => 'student', 'is_active' => true]);
        $student2 = User::factory()->create(['name' => 'Student Two', 'role' => 'student', 'is_active' => true]);

        $student1->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);
        $student2->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        $room = ChatRoom::firstOrCreate(['type' => 'course', 'course_id' => $course->id], ['name' => 'HIS-101']);
        $room->members()->sync([$student1->id, $student2->id]);

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $student1->id,
            'body' => 'Final exam schedule is out!',
        ]);

        ChatMessageReaction::create([
            'chat_message_id' => $message->id,
            'user_id' => $student1->id,
            'emoji' => '🔥',
        ]);

        ChatMessageReaction::create([
            'chat_message_id' => $message->id,
            'user_id' => $student2->id,
            'emoji' => '🔥',
        ]);

        $grouped = $message->fresh()->getGroupedReactions($student1->id);
        $this->assertCount(1, $grouped);
        $this->assertSame('🔥', $grouped[0]['emoji']);
        $this->assertSame(2, $grouped[0]['count']);
        $this->assertTrue($grouped[0]['reacted_by_me']);
        $this->assertContains('Student One', $grouped[0]['names']);
        $this->assertContains('Student Two', $grouped[0]['names']);
    }

    public function test_message_send_is_resilient_to_broadcast_server_outage(): void
    {
        $course = Course::query()->create([
            'title' => 'Resilience Test',
            'code' => 'RES-101',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['name' => 'Resilient Student', 'role' => 'student', 'is_active' => true]);
        $student->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        $room = ChatRoom::firstOrCreate(['type' => 'course', 'course_id' => $course->id], ['name' => 'RES-101']);
        $room->members()->sync([$student->id]);

        $this->actingAs($student);

        // Even with unreachable broadcasting server, message sending does not throw 500 error
        Livewire::test(Community::class)
            ->call('openRoom', $room->id)
            ->set('messageBody', 'Resilience check')
            ->call('sendMessage')
            ->assertSet('messageBody', '')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('chat_messages', [
            'chat_room_id' => $room->id,
            'user_id' => $student->id,
            'body' => 'Resilience check',
        ]);
    }

    public function test_user_first_name_accessor_extracts_first_name_correctly(): void
    {
        $user1 = User::factory()->make(['name' => 'Alice Wonder']);
        $user2 = User::factory()->make(['name' => 'Bob']);
        $user3 = User::factory()->make(['name' => '   Charlie  Brown ']);
        $user4 = User::factory()->make(['name' => '']);

        $this->assertSame('Alice', $user1->first_name);
        $this->assertSame('Bob', $user2->first_name);
        $this->assertSame('Charlie', $user3->first_name);
        $this->assertSame('Student', $user4->first_name);
    }

    public function test_direct_chat_room_display_name_uses_first_name(): void
    {
        $studentA = User::factory()->create(['name' => 'Alice Wonder', 'role' => 'student', 'is_active' => true]);
        $studentB = User::factory()->create(['name' => 'Bob Builder', 'role' => 'student', 'is_active' => true]);

        $room = ChatRoom::findOrCreateDirect($studentA->id, $studentB->id);

        $this->assertSame('Bob', $room->displayNameFor($studentA));
        $this->assertSame('Alice', $room->displayNameFor($studentB));
    }

    public function test_group_chat_displays_first_name_for_message_author(): void
    {
        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB-101',
            'is_active' => true,
        ]);

        $studentA = User::factory()->create(['name' => 'Alice Wonder', 'role' => 'student', 'is_active' => true]);
        $studentB = User::factory()->create(['name' => 'Bob Builder', 'role' => 'student', 'is_active' => true]);

        $studentA->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);
        $studentB->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        $room = ChatRoom::firstOrCreate(['type' => 'course', 'course_id' => $course->id], ['name' => 'WEB-101']);
        $room->members()->sync([$studentA->id, $studentB->id]);

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $studentA->id,
            'body' => 'Welcome everyone!',
        ]);

        $this->actingAs($studentB);

        Livewire::test(Community::class)
            ->call('openRoom', $room->id)
            ->assertSee('Alice')
            ->assertDontSee('Alice Wonder')
            ->assertSee('Reply')
            ->assertSee('Copy');
    }

    public function test_leaderboard_renders_top_5_with_collapsible_more_and_xp_earned_breakdown(): void
    {
        // Create 8 students with various XP
        $students = collect();
        for ($i = 1; $i <= 8; $i++) {
            $students->push(User::factory()->create([
                'name' => "Ranked Student {$i}",
                'role' => 'student',
                'lifetime_xp' => (10 - $i) * 100, // 900, 800, 700, 600, 500, 400, 300, 200
                'spendable_coins' => 50,
            ]));
        }

        $viewer = $students->last(); // 8th student (rank #8)

        // Give viewer a badge
        $badge = \App\Models\Badge::firstOrCreate(
            ['key' => 'first_perfect_quiz'],
            [
                'name' => 'Perfectionist',
                'description' => 'Score 100% on a quiz',
                'icon' => 'check-badge',
                'xp_reward' => 50,
            ]
        );
        $viewer->badges()->syncWithoutDetaching([$badge->id => ['earned_at' => now()]]);


        $this->actingAs($viewer);

        // Record an XP transaction for the viewer
        \App\Models\XpTransaction::create([
            'user_id' => $viewer->id,
            'points' => 200,
            'amount_xp' => 200,
            'amount_coins' => 50,
            'activity_type' => 'quiz_passed',
            'source' => 'quiz_passed',
            'description' => 'Passed Physics Midterm Quiz',
        ]);

        Livewire::test(Community::class)
            ->set('tab', 'leaderboard')
            ->assertSee('Leaderboard')
            ->assertSee('Top 8 Students')
            ->assertSee('Ranked Student 1')
            ->assertSee('Ranked Student 5')
            ->assertSee('Collapse to Top 5')
            ->assertSee('View More (Rank 6–8)')
            // Pinned viewer row is visible
            ->assertSee('Ranked Student 8 (you)')
            // XP Earned & Badges collapsible section is visible
            ->assertSee('XP Earned')
            ->assertSee('Badges')
            ->assertSee('+200 XP')
            ->assertSee('Unlocked Badges')
            ->assertSee('Perfectionist')
            ->assertSee('Recent Point Activity')
            ->assertSee('Passed Physics Midterm Quiz');

    }
}



