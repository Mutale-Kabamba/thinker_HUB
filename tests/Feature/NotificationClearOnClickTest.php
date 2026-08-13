<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\LearningMaterial;
use App\Models\User;
use App\Notifications\AssignmentAssignedNotification;
use App\Notifications\MaterialPublishedNotification;
use App\Notifications\SessionScheduledNotification;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationClearOnClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_clear_notification_on_click(): void
    {
        $user = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $notification = DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => AssignmentAssignedNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'format' => 'filament',
                'title' => 'New assignment assigned',
                'body' => 'Web Dev Project 1',
            ],
            'read_at' => null,
        ]);

        $this->assertEquals(1, $user->notifications()->count());

        $response = $this->actingAs($user)->postJson(route('notifications.clear', $notification->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(0, $user->fresh()->notifications()->count());
    }

    public function test_authenticated_user_can_mark_notification_as_read_on_click(): void
    {
        $user = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $notification = DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => AssignmentAssignedNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'format' => 'filament',
                'title' => 'New assignment assigned',
                'body' => 'Web Dev Project 1',
            ],
            'read_at' => null,
        ]);

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user)->postJson(route('notifications.read', $notification->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_clear_another_users_notification(): void
    {
        $user1 = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $user2 = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $notification = DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => AssignmentAssignedNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $user1->id,
            'data' => [
                'format' => 'filament',
                'title' => 'Secret alert',
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user2)->postJson(route('notifications.clear', $notification->id));
        $response->assertStatus(200);

        // Notification must still belong to user1 untouched
        $this->assertEquals(1, $user1->fresh()->notifications()->count());
    }

    public function test_notification_actions_include_mark_as_read_directive(): void
    {
        $course = Course::create([
            'title' => 'Fullstack Laravel',
            'code' => 'LAR101',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        // 1. Assignment notification
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Build an API',
            'due_date' => now()->addDays(5),
            'max_score' => 100,
        ]);
        $assignmentNotif = new AssignmentAssignedNotification($assignment);
        $assignmentData = $assignmentNotif->toArray($student);
        $this->assertTrue($assignmentData['actions'][0]['shouldMarkAsRead'] ?? false);

        // 2. Submission reviewed notification
        $gradedNotif = new SubmissionGradedNotification('assignment', 'Build an API', 95, 'Great job!');
        $gradedData = $gradedNotif->toArray($student);
        $this->assertTrue($gradedData['actions'][0]['shouldMarkAsRead'] ?? false);

        // 3. Material notification
        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Week 1 Cheatsheet',
        ]);
        $materialNotif = new MaterialPublishedNotification($material);
        $materialData = $materialNotif->toArray($student);
        $this->assertTrue($materialData['actions'][0]['shouldMarkAsRead'] ?? false);

        // 4. Session scheduled notification
        $session = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Live Q&A',
            'session_date' => now()->addDays(2),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
        $sessionNotif = new SessionScheduledNotification($session);
        $sessionData = $sessionNotif->toArray($student);
        $this->assertTrue($sessionData['actions'][0]['shouldMarkAsRead'] ?? false);
    }
}
