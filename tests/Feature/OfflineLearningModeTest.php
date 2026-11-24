<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\ScormAttempt;
use App\Models\ScormPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineLearningModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_can_download_offline_course_bundle_and_open_offline_library(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $outsider = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $course = Course::create(['title' => 'Offline Ready Python', 'code' => 'PY-OFF', 'is_active' => true]);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        Lesson::create([
            'course_id' => $course->id,
            'title' => 'Lesson 1: Offline Reading & Video',
            'type' => 'video',
            'order_column' => 1,
            'low_bandwidth_video_url' => '/storage/videos/py-low.mp4',
            'reading_body' => '<p>Python basics offline notes</p>',
            'resource_files' => [['name' => 'Cheatsheet.pdf', 'path' => 'docs/cheatsheet.pdf']],
        ]);

        // Unenrolled student is forbidden
        $this->actingAs($outsider)
            ->getJson(route('offline.course.bundle', $course))
            ->assertForbidden();

        // Enrolled student receives structured bundle + cacheable asset list
        $this->actingAs($student)
            ->getJson(route('offline.course.bundle', $course))
            ->assertOk()
            ->assertJsonPath('course.title', 'Offline Ready Python')
            ->assertJsonPath('course.lessons.0.video_url', '/storage/videos/py-low.mp4')
            ->assertJsonFragment(['/storage/videos/py-low.mp4']);

        // Standalone offline library shell loads cleanly and is not intercepted by course.player
        $this->actingAs($student)
            ->get(route('offline.library'))
            ->assertOk()
            ->assertSee('Offline Learning Library');
    }

    public function test_offline_sync_endpoint_replays_queued_lesson_video_scorm_and_download_events(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $course = Course::create(['title' => 'Sync Course', 'code' => 'SYN-101', 'is_active' => true]);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $lesson1 = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Intro Lesson',
            'type' => 'reading',
            'order_column' => 1,
        ]);
        $lesson2 = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Second Lesson',
            'type' => 'video',
            'order_column' => 2,
        ]);

        $package = ScormPackage::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson2->id,
            'title' => 'Offline SCORM Module',
            'standard' => ScormPackage::STANDARD_SCORM_12,
            'storage_disk' => 'public',
            'storage_path' => 'scorm/test',
            'entry_point' => 'index.html',
        ]);

        $attempt = ScormAttempt::create([
            'scorm_package_id' => $package->id,
            'user_id' => $student->id,
            'lesson_id' => $lesson2->id,
            'attempt_number' => 1,
            'lesson_status' => 'incomplete',
        ]);

        $this->actingAs($student)
            ->postJson(route('offline.sync'), [
                'events' => [
                    ['id' => 'e1', 'type' => 'lesson_complete', 'payload' => ['lesson_id' => $lesson1->id]],
                    ['id' => 'e2', 'type' => 'video_progress', 'payload' => ['lesson_id' => $lesson2->id, 'seconds' => 145]],
                    ['id' => 'e3', 'type' => 'scorm_commit', 'payload' => ['attempt_id' => $attempt->id, 'cmi' => ['cmi.core.lesson_status' => 'completed', 'cmi.core.score.raw' => '88']]],
                    ['id' => 'e4', 'type' => 'resource_download', 'payload' => ['lesson_id' => $lesson1->id, 'file_name' => 'Notes.pdf']],
                ],
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'synced_count' => 4,
            ]);

        $this->assertTrue(LessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson1->id)->value('is_completed'));
        $this->assertSame(145, (int) LessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson2->id)->value('last_watched_second'));
        $this->assertSame('completed', $attempt->fresh()->lesson_status);
        $this->assertDatabaseHas('resource_downloads', ['user_id' => $student->id, 'file_name' => 'Notes.pdf']);
    }
}
