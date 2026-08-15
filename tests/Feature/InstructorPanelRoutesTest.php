<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorPanelRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));
    }

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
            '/teach/student-results',
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
            '/teach/claim-item-resource/claim-items',
            '/teach/claim-item-resource/claim-items/create',
            '/teach/claim-request-resource/claim-requests',
            '/teach/course-gamification-rule-resource/course-gamification-rules',
            '/teach/course-gamification-rule-resource/course-gamification-rules/create',
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
            '/teach/student-results',
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
            '/teach/claim-item-resource/claim-items',
            '/teach/claim-request-resource/claim-requests',
            '/teach/course-gamification-rule-resource/course-gamification-rules',
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

    public function test_instructor_can_edit_and_grade_assignment_submission(): void
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

        $assignment = \App\Models\Assignment::query()->create([
            'name' => 'Physics Homework 1',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $submission = \App\Models\AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'content' => 'Here is my solution',
        ]);

        $this->actingAs($instructor);

        \Livewire\Livewire::test(\App\Filament\Instructor\Resources\AssignmentSubmissionResource\Pages\EditAssignmentSubmission::class, [
            'record' => $submission->getRouteKey(),
        ])
            ->fillForm([
                'status' => 'Graded',
                'grade' => 95,
                'feedback' => 'Excellent work!',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'status' => 'Graded',
            'grade' => 95,
            'feedback' => 'Excellent work!',
        ]);
    }

    public function test_instructor_can_edit_and_grade_assessment_submission(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Test Chemistry',
            'code' => 'CHM-101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $assessment = \App\Models\Assessment::query()->create([
            'user_id' => $student->id,
            'name' => 'Chemistry Midterm',
            'course_id' => $course->id,
            'target_level' => 'Beginner',
            'date_given' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $submission = \App\Models\AssessmentSubmission::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'content' => 'Here is my midterm answer',
        ]);

        $this->actingAs($instructor);

        \Livewire\Livewire::test(\App\Filament\Instructor\Resources\AssessmentSubmissionResource\Pages\EditAssessmentSubmission::class, [
            'record' => $submission->getRouteKey(),
        ])
            ->fillForm([
                'status' => 'Graded',
                'score' => 88,
                'feedback' => 'Great performance.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('assessment_submissions', [
            'id' => $submission->id,
            'status' => 'Graded',
            'score' => 88,
            'feedback' => 'Great performance.',
        ]);
    }

    public function test_instructor_can_view_consolidated_student_results_and_breakdown(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Biology 101',
            'code' => 'BIO-101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Alice Student',
            'email' => 'alice@example.com',
            'track' => 'Beginner',
            'is_active' => true,
        ]);
        $student->enrollments()->create([
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);

        // 1. Quiz
        $quiz = \App\Models\Quiz::query()->create([
            'title' => 'Biology Quiz 1',
            'course_id' => $course->id,
            'is_active' => true,
            'pass_percentage' => 60,
        ]);
        $attempt = \App\Models\QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
            'score' => 80,
            'total_points' => 100,
            'percentage' => 80,
            'passed' => true,
        ]);

        // 2. Assignment
        $assignment = \App\Models\Assignment::query()->create([
            'name' => 'Cell Structure Lab',
            'course_id' => $course->id,
            'target_level' => 'Beginner',
            'date_given' => now(),
            'due_date' => now()->addDays(5),
        ]);
        \App\Models\AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'status' => 'Graded',
            'grade' => 90,
            'submitted_at' => now(),
            'feedback' => 'Well done on diagrams.',
        ]);

        // 3. Assessment
        $assessment = \App\Models\Assessment::query()->create([
            'user_id' => $student->id,
            'name' => 'Midterm Practical',
            'course_id' => $course->id,
            'target_level' => 'Beginner',
            'date_given' => now(),
            'due_date' => now()->addDays(7),
        ]);
        \App\Models\AssessmentSubmission::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'status' => 'Graded',
            'score' => 85,
            'submitted_at' => now(),
            'feedback' => 'Good scientific analysis.',
        ]);

        $this->actingAs($instructor);

        $component = \Livewire\Livewire::test(\App\Filament\Instructor\Pages\StudentResults::class);
        $component->assertSuccessful();

        $studentsData = $component->instance()->getStudentsData();
        $this->assertCount(1, $studentsData);

        $studentRow = $studentsData[0];
        $this->assertEquals('Alice Student', $studentRow['name']);
        $this->assertEquals(80.0, $studentRow['avg_quiz_score']);
        $this->assertEquals(90.0, $studentRow['avg_assignment_grade']);
        $this->assertEquals(85.0, $studentRow['avg_assessment_score']);
        // Overall: (80 + 90 + 85) / 3 = 85.0
        $this->assertEquals(85.0, $studentRow['overall_score']);
        $this->assertEquals('distinction', $studentRow['tier_key']);

        // Test toggle expand and tab setting
        $component->call('toggleExpand', $student->id)
            ->assertSet('expandedStudents.' . $student->id, true);

        $component->call('setTab', $student->id, 'quizzes')
            ->assertSet('activeTabs.' . $student->id, 'quizzes');

        $component->call('expandAll');
        $this->assertTrue($component->get('expandedStudents')[$student->id]);

        $component->call('collapseAll');
        $this->assertEmpty($component->get('expandedStudents'));
    }

    public function test_instructor_can_filter_student_results_and_export_csv(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course1 = Course::query()->create([
            'title' => 'Course 1',
            'code' => 'C1',
            'is_active' => true,
        ]);
        $course2 = Course::query()->create([
            'title' => 'Course 2',
            'code' => 'C2',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach([$course1->id, $course2->id]);

        $student1 = User::factory()->create([
            'role' => 'student',
            'name' => 'Alice Beginner',
            'email' => 'alice@test.com',
            'track' => 'Beginner',
            'is_active' => true,
        ]);
        $student1->enrollments()->create(['course_id' => $course1->id, 'status' => 'enrolled']);

        $student2 = User::factory()->create([
            'role' => 'student',
            'name' => 'Bob Advanced',
            'email' => 'bob@test.com',
            'track' => 'Advanced',
            'is_active' => true,
        ]);
        $student2->enrollments()->create(['course_id' => $course2->id, 'status' => 'enrolled']);

        $this->actingAs($instructor);

        $component = \Livewire\Livewire::test(\App\Filament\Instructor\Pages\StudentResults::class);
        $this->assertCount(2, $component->instance()->getStudentsData());

        // Filter by course 1
        $component->set('courseFilter', (string) $course1->id);
        $data = $component->instance()->getStudentsData();
        $this->assertCount(1, $data);
        $this->assertEquals('Alice Beginner', $data[0]['name']);

        // Filter by track
        $component->set('courseFilter', '')
            ->set('trackFilter', 'Advanced');
        $data = $component->instance()->getStudentsData();
        $this->assertCount(1, $data);
        $this->assertEquals('Bob Advanced', $data[0]['name']);

        // Search
        $component->set('trackFilter', '')
            ->set('search', 'alice');
        $data = $component->instance()->getStudentsData();
        $this->assertCount(1, $data);
        $this->assertEquals('Alice Beginner', $data[0]['name']);

        // Reset
        $component->call('resetFilters');
        $this->assertEquals('', $component->get('search'));
        $this->assertEquals('', $component->get('trackFilter'));

        // Test export CSV
        $response = $component->instance()->exportCsv();
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }

    public function test_instructor_can_view_tasks_and_results_in_descending_order(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Math 101',
            'code' => 'MTH-101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $student1 = User::factory()->create(['role' => 'student', 'name' => 'Low Scorer', 'is_active' => true]);
        $student2 = User::factory()->create(['role' => 'student', 'name' => 'Top Scorer', 'is_active' => true]);
        $student1->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);
        $student2->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        // Quiz with two attempts
        $quiz = \App\Models\Quiz::query()->create([
            'title' => 'Calculus Quiz',
            'course_id' => $course->id,
            'is_active' => true,
        ]);
        \App\Models\QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student1->id,
            'completed_at' => now(),
            'score' => 45,
            'total_points' => 100,
            'percentage' => 45,
            'passed' => false,
        ]);
        \App\Models\QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student2->id,
            'completed_at' => now(),
            'score' => 95,
            'total_points' => 100,
            'percentage' => 95,
            'passed' => true,
        ]);

        $this->actingAs($instructor);

        $component = \Livewire\Livewire::test(\App\Filament\Instructor\Pages\StudentResults::class);
        $tasksData = $component->instance()->getTasksData();

        $this->assertNotEmpty($tasksData['quizzes']);
        $quizTask = $tasksData['quizzes'][0];
        $this->assertEquals('Calculus Quiz', $quizTask['title']);

        // Assert student results are in DESCENDING order: Top Scorer (95%) first, Low Scorer (45%) second
        $this->assertCount(2, $quizTask['results']);
        $this->assertEquals('Top Scorer', $quizTask['results'][0]['student_name']);
        $this->assertEquals(95, $quizTask['results'][0]['percentage']);
        $this->assertEquals('Low Scorer', $quizTask['results'][1]['student_name']);
        $this->assertEquals(45, $quizTask['results'][1]['percentage']);

        // Test switching task filters and view modes
        $component->call('setViewMode', 'tasks')
            ->assertSet('viewMode', 'tasks');

        $component->call('setTaskType', 'quizzes')
            ->assertSet('taskTypeFilter', 'quizzes');

        // Test clicking horizontal category card to open full details
        $component->call('selectCategory', 'assignments')
            ->assertSet('activeCategory', 'assignments');

        $component->call('selectCategory', 'assessments')
            ->assertSet('activeCategory', 'assessments');

        $component->call('selectCategory', 'quizzes')
            ->assertSet('activeCategory', 'quizzes');

        // Create an untaken quiz (0 attempts) - should NOT show up
        $untakenQuiz = \App\Models\Quiz::query()->create([
            'title' => 'Algebra Quiz (Untaken)',
            'course_id' => $course->id,
            'is_active' => true,
        ]);

        $tasksData = $component->instance()->getTasksData();
        // Only the taken quiz should appear
        $this->assertCount(1, $tasksData['quizzes']);
        $this->assertEquals('Calculus Quiz', $tasksData['quizzes'][0]['title']);

        // Now student takes Algebra Quiz with newer timestamp -> FILO test
        \App\Models\QuizAttempt::query()->create([
            'quiz_id' => $untakenQuiz->id,
            'user_id' => $student1->id,
            'completed_at' => now()->addMinutes(10),
            'score' => 88,
            'total_points' => 100,
            'percentage' => 88,
            'passed' => true,
        ]);

        $tasksData = $component->instance()->getTasksData();
        // Now both taken quizzes appear, in FILO order (Algebra Quiz first because it's most recently taken)
        $this->assertCount(2, $tasksData['quizzes']);
        $this->assertEquals('Algebra Quiz (Untaken)', $tasksData['quizzes'][0]['title']);
        $this->assertEquals('Calculus Quiz', $tasksData['quizzes'][1]['title']);

        // Test collapsible toggling
        $this->assertTrue($component->instance()->isTaskExpanded($tasksData['quizzes'][0]['key']));
        $component->call('toggleTask', $tasksData['quizzes'][0]['key']);
        $this->assertFalse($component->instance()->isTaskExpanded($tasksData['quizzes'][0]['key']));
        $component->call('toggleTask', $tasksData['quizzes'][0]['key']);
        $this->assertTrue($component->instance()->isTaskExpanded($tasksData['quizzes'][0]['key']));
    }
}





