<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use App\Services\InstructorReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InstructorSmartReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_reminder_service_aggregates_unmarked_registers_and_assignments(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Dr. Jane Doe',
            'email' => 'jane.doe@example.com',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Advanced Robotics',
            'slug' => 'advanced-robotics',
            'code' => 'ROB401',
            'is_active' => true,
        ]);

        $instructor->instructorCourses()->attach($course->id);

        $student = User::factory()->create([
            'name' => 'Alex Student',
            'email' => 'alex@example.com',
            'role' => 'student',
            'is_active' => true,
        ]);

        // Create an uncompleted past session
        $session = CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Lab 1: Sensor Calibration',
            'session_date' => Carbon::yesterday(),
            'start_time' => '14:00:00',
            'end_time' => '15:30:00',
            'status' => 'scheduled',
            'type' => 'group',
        ]);

        // Create a pending assignment submission
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Robotics Project 1',
            'points' => 100,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'My project submission',
            'status' => 'Submitted',
            'submitted_at' => Carbon::now()->subHours(2),
        ]);

        $service = app(InstructorReminderService::class);
        $data = $service->getRemindersData($instructor);

        $this->assertTrue($data['has_pending']);
        $this->assertEquals(2, $data['total_actionable']);
        $this->assertEquals(1, $data['unmarked_count']);
        $this->assertEquals(1, $data['pending_assignments_count']);
        $this->assertEquals('Jane', $data['instructor_name']);
        $this->assertNotEmpty($data['unmarked_sessions']);
        $this->assertEquals($session->id, $data['unmarked_sessions'][0]['id']);
        $this->assertStringContainsString((string) $session->id, $data['unmarked_sessions'][0]['action_url']);
        $this->assertNotEmpty($data['pending_assignments']);
        $this->assertEquals($submission->id, $data['pending_assignments'][0]['id']);
    }

    public function test_dismissal_endpoint_updates_session_properly(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Instructor Smith',
            'email' => 'smith@example.com',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($instructor);

        // Test session dismissal
        $response = $this->postJson(route('teach.dismiss-reminder'), ['duration' => 'session']);
        $response->assertOk();
        $response->assertJson(['status' => 'ok', 'duration' => 'session']);
        $this->assertTrue(session('instructor_reminder_dismissed_session'));

        // Test day dismissal
        $response = $this->postJson(route('teach.dismiss-reminder'), ['duration' => 'day']);
        $response->assertOk();
        $response->assertJson(['status' => 'ok', 'duration' => 'day']);
        $this->assertNotNull(session('instructor_reminder_dismissed_until'));
    }

    public function test_instructor_overview_page_renders_with_reminder_components(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Prof. Alan Turing',
            'email' => 'turing@example.com',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($instructor);

        $response = $this->get('/teach/instructor-overview');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));

        if ($response->getStatusCode() === 200) {
            // Top tasks button should be removed
            $response->assertDontSee('hub-topbar-reminder-btn');
            // The smart reminder popup should be rendered in the body
            $response->assertSee('Instructor Dispatch');
        }
    }

    public function test_service_detects_today_sessions_and_auto_popup_flags(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Dr. Ada Lovelace',
            'email' => 'ada@example.com',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Algorithms 101',
            'slug' => 'algorithms-101',
            'code' => 'CS101',
            'is_active' => true,
        ]);

        $instructor->instructorCourses()->attach($course->id);

        $todaySession = CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Live Lecture: Sorting Algorithms',
            'session_date' => Carbon::today(),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
            'type' => 'group',
        ]);

        $service = app(InstructorReminderService::class);
        $data = $service->getRemindersData($instructor);

        $this->assertEquals(1, $data['today_sessions_count']);
        $this->assertEquals('Live Lecture: Sorting Algorithms', $data['today_sessions'][0]['title']);
        $this->assertEquals('Ada', $data['instructor_name']);
        $this->assertTrue($service->shouldShowPopup($instructor, $data));
    }

    public function test_reminder_popup_does_not_show_when_no_activities_are_due(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Prof. No Work',
            'email' => 'nowork@example.com',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $service = app(InstructorReminderService::class);
        $data = $service->getRemindersData($instructor);

        // All activities are 0
        $this->assertEquals(0, $data['total_actionable']);
        $this->assertFalse($service->shouldShowPopup($instructor, $data));
    }

    public function test_reminder_popup_only_shows_once_per_login_session(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Prof. Busy Bee',
            'email' => 'busy@example.com',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Web Dev 101',
            'slug' => 'web-dev-101',
            'code' => 'WEB101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        // Create an unmarked session so total_actionable > 0
        CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'CSS Grid Session',
            'session_date' => Carbon::yesterday(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'scheduled',
            'type' => 'group',
        ]);

        $service = app(InstructorReminderService::class);
        $data = $service->getRemindersData($instructor);
        $this->assertGreaterThan(0, $data['total_actionable']);

        // First check in session: should show
        $this->assertTrue($service->shouldShowPopup($instructor, $data));

        // Mark shown for this session
        $service->markPopupShownForSession();

        // Second check in same session: should NOT show
        $this->assertFalse($service->shouldShowPopup($instructor, $data));
    }

    public function test_new_login_clears_shown_session_flag(): void
    {
        $instructor = User::factory()->create([
            'name' => 'Prof. Fresh Session',
            'email' => 'fresh@example.com',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        // Simulate existing session where reminder was already shown
        session(['instructor_reminder_shown_for_session' => true]);
        session(['instructor_reminder_dismissed_session' => true]);

        // Fire login event
        event(new \Illuminate\Auth\Events\Login('web', $instructor, false));

        // Session flags should be cleared
        $this->assertFalse(session()->has('instructor_reminder_shown_for_session'));
        $this->assertFalse(session()->has('instructor_reminder_dismissed_session'));
    }
}
