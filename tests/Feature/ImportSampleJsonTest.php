<?php

namespace Tests\Feature;

use App\Filament\Actions\ImportQuizzesAction;
use App\Filament\Actions\ImportSessionsAction;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportSampleJsonTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_sample_json_is_valid_and_can_be_mapped(): void
    {
        $path = public_path('samples/imports/courses.sample.json');
        $this->assertFileExists($path);

        $json = file_get_contents($path);
        $this->assertJson($json);

        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data));

        foreach ($data as $courseData) {
            $this->assertNotEmpty($courseData['title']);
            $this->assertNotEmpty($courseData['code']);
            $this->assertArrayHasKey('offering_mode', $courseData);
            $this->assertArrayHasKey('is_open_enrollment', $courseData);
            $this->assertArrayHasKey('is_for_kids', $courseData);
        }
    }

    public function test_quizzes_sample_json_is_valid_and_imports_successfully(): void
    {
        $path = public_path('samples/imports/quizzes.sample.json');
        $this->assertFileExists($path);

        $json = file_get_contents($path);
        $this->assertJson($json);

        $data = json_decode($json, true);
        $this->assertIsArray($data);

        // Pre-create courses for the sample quizzes
        Course::query()->create(['title' => 'Web Development', 'code' => 'WEB-101', 'is_active' => true]);
        Course::query()->create(['title' => 'Python Foundations', 'code' => 'PY-201', 'is_active' => true]);

        $refMethod = new \ReflectionMethod(ImportQuizzesAction::class, 'processQuizRow');
        $refMethod->setAccessible(true);

        foreach ($data as $index => $quizRow) {
            $result = $refMethod->invoke(null, $quizRow, $index, null, true);
            $this->assertContains($result, ['created', 'updated']);
        }

        $this->assertEquals(2, Quiz::query()->count());
        $webQuiz = Quiz::query()->where('title', 'HTML, CSS & JavaScript Fundamentals Assessment')->first();
        $this->assertNotNull($webQuiz);
        $this->assertNotNull($webQuiz->course_intake_id);
        $this->assertEquals('Cohort Alpha', $webQuiz->intake->name);
        $this->assertGreaterThanOrEqual(4, $webQuiz->questions()->count());
    }

    public function test_sessions_sample_json_is_valid_and_imports_all_delivery_modes(): void
    {
        $path = public_path('samples/imports/sessions.sample.json');
        $this->assertFileExists($path);

        $json = file_get_contents($path);
        $this->assertJson($json);

        $data = json_decode($json, true);
        $this->assertIsArray($data);

        // Pre-create course and student
        $courseWeb = Course::query()->create(['title' => 'Web Development', 'code' => 'WEB-101', 'is_active' => true]);
        $coursePy = Course::query()->create(['title' => 'Python Foundations', 'code' => 'PY-201', 'is_active' => true]);
        User::factory()->create(['email' => 'student@example.com', 'role' => 'student']);

        $refMethod = new \ReflectionMethod(ImportSessionsAction::class, 'processRow');
        $refMethod->setAccessible(true);

        foreach ($data as $index => $sessionRow) {
            $result = $refMethod->invoke(null, $sessionRow, $index, null, null);
            $this->assertContains($result, ['created', 'updated']);
        }

        $this->assertGreaterThanOrEqual(7, CourseSession::query()->count());

        // Verify virtual
        $virtual = CourseSession::query()->where('delivery_mode', CourseSession::DELIVERY_LIVE_VIRTUAL)->first();
        $this->assertNotNull($virtual);
        $this->assertNotEmpty($virtual->meeting_link);

        // Verify physical
        $physical = CourseSession::query()->where('delivery_mode', CourseSession::DELIVERY_LIVE_PHYSICAL)->first();
        $this->assertNotNull($physical);
        $this->assertStringContainsString('Campus', $physical->physical_address);

        // Verify video
        $video = CourseSession::query()->where('delivery_mode', CourseSession::DELIVERY_VIDEO)->first();
        $this->assertNotNull($video);
        $this->assertNotEmpty($video->video_url);
        $this->assertEquals(45, $video->required_duration_minutes);

        // Verify read
        $read = CourseSession::query()->where('delivery_mode', CourseSession::DELIVERY_READ)->first();
        $this->assertNotNull($read);
        $this->assertNotEmpty($read->reading_content);
        $this->assertEquals(30, $read->required_duration_minutes);
    }
}
