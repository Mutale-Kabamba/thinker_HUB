<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\ResourceVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MigrateActivitiesToActiveIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_moves_all_activities_and_enrollments_to_active_intake(): void
    {
        $course = Course::query()->create([
            'title' => 'Advanced Robotics',
            'code' => 'ROB-401',
            'is_active' => true,
        ]);

        $activeIntake = CourseIntake::query()->create([
            'course_id' => $course->id,
            'name' => 'Cohort 2026',
            'start_date' => now(),
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        // Create activities without intake assigned
        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'title' => 'Intro Session',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'course_intake_id' => null,
        ]);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'name' => 'Robotics Project 1',
            'date_given' => now(),
            'due_date' => now()->addDays(7),
            'course_intake_id' => null,
        ]);

        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'name' => 'Midterm Assessment',
            'target_level' => 'Advanced',
            'date_given' => now(),
            'due_date' => now()->addDays(7),
            'course_intake_id' => null,
        ]);

        $quiz = Quiz::query()->create([
            'course_id' => $course->id,
            'title' => 'Circuit Quiz',
            'course_intake_id' => null,
        ]);

        $material = LearningMaterial::query()->create([
            'course_id' => $course->id,
            'title' => 'Robotics Handbook',
            'course_intake_id' => null,
        ]);

        $video = ResourceVideo::query()->create([
            'course_id' => $course->id,
            'title' => 'Lab Setup Video',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'course_intake_id' => null,
        ]);

        $enrollment = Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'course_intake_id' => null,
        ]);

        // Run the migration
        $migration = require database_path('migrations/2026_08_25_100000_migrate_activities_and_enrollments_to_active_intake.php');
        $migration->up();

        // Verify all activities now point to active intake
        $this->assertEquals($activeIntake->id, $session->fresh()->course_intake_id);
        $this->assertEquals($activeIntake->id, $assignment->fresh()->course_intake_id);
        $this->assertEquals($activeIntake->id, $assessment->fresh()->course_intake_id);
        $this->assertEquals($activeIntake->id, $quiz->fresh()->course_intake_id);
        $this->assertEquals($activeIntake->id, $material->fresh()->course_intake_id);
        $this->assertEquals($activeIntake->id, $video->fresh()->course_intake_id);
        $this->assertEquals($activeIntake->id, $enrollment->fresh()->course_intake_id);
    }
}
