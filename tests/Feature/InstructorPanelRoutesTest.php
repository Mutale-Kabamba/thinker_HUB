<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorPanelRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_access_all_teach_pages_and_resources(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Test Physics',
            'code' => 'PHY-101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
        $student->enrollments()->create([
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);

        $this->actingAs($instructor);

        $urls = [
            '/teach/instructor-overview',
            '/teach/analytics',
            '/teach/schedule',
            '/teach/broadcasts',
            '/teach/course-resource/courses',
            '/teach/course-resource/courses/' . $course->id . '/edit',
            '/teach/resource-video-resource/resource-videos',
            '/teach/resource-video-resource/resource-videos/create',
            '/teach/learning-material-resource/learning-materials',
            '/teach/learning-material-resource/learning-materials/create',
            '/teach/assessment-resource/assessments',
            '/teach/assessment-resource/assessments/create',
            '/teach/assessment-submission-resource/assessment-submissions',
            '/teach/assignment-resource/assignments',
            '/teach/assignment-resource/assignments/create',
            '/teach/assignment-submission-resource/assignment-submissions',
            '/teach/quiz-resource/quizzes',
            '/teach/quiz-resource/quizzes/create',
            '/teach/students',
            '/teach/students/create',
            '/teach/students/' . $student->id,
            '/teach/students/' . $student->id . '/edit',
            '/teach/course-session-resource/course-sessions',
            '/teach/course-session-resource/course-sessions/create',
            '/teach/hub-posts',
            '/teach/hub-posts/create',
            '/teach/settings',
        ];

        foreach ($urls as $url) {
            $response = $this->withoutExceptionHandling()->get($url);
            $response->assertSuccessful("Failed accessing {$url} with status: " . $response->status());
        }
    }

    public function test_instructor_with_no_courses_can_access_all_pages_without_errors(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($instructor);

        $urls = [
            '/teach/instructor-overview',
            '/teach/analytics',
            '/teach/schedule',
            '/teach/broadcasts',
            '/teach/course-resource/courses',
            '/teach/resource-video-resource/resource-videos',
            '/teach/learning-material-resource/learning-materials',
            '/teach/assessment-resource/assessments',
            '/teach/assessment-submission-resource/assessment-submissions',
            '/teach/assignment-resource/assignments',
            '/teach/assignment-submission-resource/assignment-submissions',
            '/teach/quiz-resource/quizzes',
            '/teach/students',
            '/teach/course-session-resource/course-sessions',
            '/teach/hub-posts',
            '/teach/settings',
        ];

        foreach ($urls as $url) {
            $response = $this->withoutExceptionHandling()->get($url);
            $response->assertSuccessful("Failed accessing {$url} with status: " . $response->status());
        }
    }
}
