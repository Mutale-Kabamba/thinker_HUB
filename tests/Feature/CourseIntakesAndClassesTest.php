<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseIntakesAndClassesTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_offering_mode_defaults_to_once_off(): void
    {
        $course = Course::create([
            'title' => 'Introduction to Python',
            'code' => 'PY-101',
            'description' => 'A great beginner course.',
            'is_active' => true,
        ]);

        $this->assertTrue($course->isOnceOff());
        $this->assertFalse($course->isOngoing());
        $this->assertSame('once_off', $course->offering_mode);
    }

    public function test_course_can_be_configured_as_ongoing_with_intakes(): void
    {
        $course = Course::create([
            'title' => 'Full-Stack Web Development',
            'code' => 'FS-202',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $this->assertTrue($course->isOngoing());
        $this->assertFalse($course->isOnceOff());

        $intake1 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Intake 1 - January 2026',
            'start_date' => '2026-01-10',
            'end_date' => '2026-03-30',
            'next_intake_start_date' => '2026-04-15',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $this->assertSame($intake1->id, $course->activeIntake->id);
        $this->assertStringContainsString('Jan 10, 2026 – Mar 30, 2026', $intake1->formattedDateRange());
        $this->assertSame('Apr 15, 2026', $intake1->formattedNextIntake());
    }

    public function test_activating_an_intake_deactivates_previous_active_intakes(): void
    {
        $course = Course::create([
            'title' => 'Graphic Design Masterclass',
            'code' => 'GD-301',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake1 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Batch 1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-28',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $intake2 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Batch 2',
            'start_date' => '2026-03-01',
            'end_date' => '2026-04-30',
            'status' => CourseIntake::STATUS_UPCOMING,
            'is_active' => false,
        ]);

        $intake2->activate();

        $intake1->refresh();
        $intake2->refresh();

        $this->assertFalse($intake1->is_active);
        $this->assertSame(CourseIntake::STATUS_COMPLETED, $intake1->status);

        $this->assertTrue($intake2->is_active);
        $this->assertSame(CourseIntake::STATUS_ACTIVE, $intake2->status);
    }

    public function test_archiving_an_intake_sets_archived_status_and_timestamp(): void
    {
        $course = Course::create([
            'title' => 'Data Science & AI',
            'code' => 'DS-401',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort Alpha',
            'start_date' => '2026-01-15',
            'end_date' => '2026-04-15',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $intake->archive();
        $intake->refresh();

        $this->assertTrue($intake->isArchived());
        $this->assertFalse($intake->is_active);
        $this->assertSame(CourseIntake::STATUS_ARCHIVED, $intake->status);
        $this->assertNotNull($intake->archived_at);
    }

    public function test_enrollment_associates_with_active_intake_and_preserves_archive_history(): void
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'title' => 'Cybersecurity Essentials',
            'code' => 'SEC-501',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake1 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort 2026-Q1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'next_intake_start_date' => '2026-04-15',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        // Student 1 enrolls in Intake 1
        $enrollment1 = Enrollment::create([
            'user_id' => $student1->id,
            'course_id' => $course->id,
            'course_intake_id' => $course->activeIntake->id,
        ]);

        $this->assertSame($intake1->id, $enrollment1->course_intake_id);
        $this->assertSame(1, $intake1->enrollments()->count());

        // Intake 1 is completed and archived, and Intake 2 is launched on a blank slate
        $intake1->archive();

        $intake2 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort 2026-Q2',
            'start_date' => '2026-04-15',
            'end_date' => '2026-07-15',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);
        $intake2->activate();

        // Intake 2 starts with a blank slate (0 enrollments)
        $this->assertSame(0, $intake2->enrollments()->count());

        // Student 2 enrolls in the new active intake (Intake 2)
        $course->refresh();
        $enrollment2 = Enrollment::create([
            'user_id' => $student2->id,
            'course_id' => $course->id,
            'course_intake_id' => $course->activeIntake->id,
        ]);

        $this->assertSame($intake2->id, $enrollment2->course_intake_id);
        $this->assertSame(1, $intake2->enrollments()->count());

        // Student 1's historical enrollment is preserved in archived Intake 1
        $enrollment1->refresh();
        $this->assertSame($intake1->id, $enrollment1->course_intake_id);
        $this->assertTrue($enrollment1->intake->isArchived());
    }

    public function test_course_details_page_renders_intake_schedule_and_next_intake_date(): void
    {
        $course = Course::create([
            'title' => 'Digital Marketing Accelerator',
            'code' => 'MKT-101',
            'offering_mode' => 'ongoing',
            'overview' => 'Learn digital marketing strategies.',
            'timeline' => '8 Weeks',
            'is_active' => true,
        ]);

        CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Summer 2026 Cohort',
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
            'next_intake_start_date' => '2026-09-01',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $response = $this->get(route('landing.courses.show', ['course' => $course->id, 'slug' => 'digital-marketing-accelerator']));

        $response->assertOk();
        $response->assertSee('Summer 2026 Cohort');
        $response->assertSee('Ongoing Course');
        $response->assertSee('Next Intake Starts');
        $response->assertSee('Sep 1, 2026');
        $response->assertSee('Jun 1, 2026 – Jul 31, 2026');
    }
}
