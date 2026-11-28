<?php

namespace Tests\Feature;

use App\Filament\Actions\ExportQuizzesAction;
use App\Filament\Actions\ImportQuizzesAction;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\QuizExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class QuizExportTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::query()->create([
            'title' => 'Web Development 101',
            'code' => 'WEB-101',
            'slug' => 'web-development-101',
            'is_active' => true,
        ]);

        $this->quiz = Quiz::query()->create([
            'course_id' => $this->course->id,
            'title' => 'JavaScript Basics Quiz',
            'description' => 'Test your basic JS knowledge',
            'time_limit_minutes' => 30,
            'shuffle_questions' => true,
            'show_results' => true,
            'pass_percentage' => 70,
            'is_active' => true,
        ]);

        // MCQ Question
        $mcq = QuizQuestion::query()->create([
            'quiz_id' => $this->quiz->id,
            'type' => 'multiple_choice',
            'question' => 'What is 2 + 2 in JS?',
            'explanation' => 'Basic arithmetic',
            'points' => 2,
            'sort_order' => 1,
        ]);

        QuizOption::query()->create([
            'quiz_question_id' => $mcq->id,
            'option_text' => '3',
            'is_correct' => false,
            'sort_order' => 1,
        ]);

        QuizOption::query()->create([
            'quiz_question_id' => $mcq->id,
            'option_text' => '4',
            'is_correct' => true,
            'sort_order' => 2,
        ]);

        // Theory Question
        QuizQuestion::query()->create([
            'quiz_id' => $this->quiz->id,
            'type' => 'theory',
            'question' => 'Explain event bubbling in the DOM.',
            'explanation' => 'Events propagate up the DOM tree.',
            'points' => 5,
            'sort_order' => 2,
        ]);

        // Practical Question
        QuizQuestion::query()->create([
            'quiz_id' => $this->quiz->id,
            'type' => 'practical',
            'question' => 'Write a function that reverses a string.',
            'explanation' => 'return str.split("").reverse().join("");',
            'points' => 5,
            'sort_order' => 3,
        ]);
    }

    public function test_quiz_export_service_exports_valid_json_matching_import_schema(): void
    {
        $service = app(QuizExportService::class);
        $json = $service->exportToJson(collect([$this->quiz]));

        $this->assertJson($json);

        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        $exportedQuiz = $data[0];
        $this->assertEquals('WEB-101', $exportedQuiz['course_code']);
        $this->assertEquals('JavaScript Basics Quiz', $exportedQuiz['title']);
        $this->assertEquals(30, $exportedQuiz['time_limit_minutes']);
        $this->assertTrue($exportedQuiz['shuffle_questions']);
        $this->assertTrue($exportedQuiz['show_results']);
        $this->assertEquals(70, $exportedQuiz['pass_percentage']);
        $this->assertTrue($exportedQuiz['is_active']);

        $this->assertCount(3, $exportedQuiz['questions']);

        // First is MCQ
        $q1 = $exportedQuiz['questions'][0];
        $this->assertEquals('multiple_choice', $q1['type']);
        $this->assertEquals('What is 2 + 2 in JS?', $q1['question']);
        $this->assertCount(2, $q1['options']);
        $this->assertFalse($q1['options'][0]['is_correct']);
        $this->assertTrue($q1['options'][1]['is_correct']);

        // Second is theory
        $q2 = $exportedQuiz['questions'][1];
        $this->assertEquals('theory', $q2['type']);
        $this->assertEquals('Explain event bubbling in the DOM.', $q2['question']);
        $this->assertArrayNotHasKey('options', $q2);

        // Third is practical
        $q3 = $exportedQuiz['questions'][2];
        $this->assertEquals('practical', $q3['type']);
        $this->assertEquals('Write a function that reverses a string.', $q3['question']);
    }

    public function test_exported_json_can_be_reimported_via_import_quizzes_action(): void
    {
        $service = app(QuizExportService::class);
        $json = $service->exportToJson(collect([$this->quiz]));
        $data = json_decode($json, true);

        // Delete the quiz and its questions
        $this->quiz->questions()->delete();
        $this->quiz->delete();
        $this->assertDatabaseMissing('quizzes', ['title' => 'JavaScript Basics Quiz']);

        // Re-import using reflection to access ImportQuizzesAction::processQuizRow
        $refMethod = new \ReflectionMethod(ImportQuizzesAction::class, 'processQuizRow');
        $refMethod->setAccessible(true);

        $result = $refMethod->invoke(null, $data[0], 0, null, true);
        $this->assertEquals('created', $result);

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $this->course->id,
            'title' => 'JavaScript Basics Quiz',
            'time_limit_minutes' => 30,
        ]);

        $importedQuiz = Quiz::query()->where('title', 'JavaScript Basics Quiz')->first();
        $this->assertNotNull($importedQuiz);
        $this->assertEquals(3, $importedQuiz->questions()->count());

        $mcq = $importedQuiz->questions()->where('type', 'multiple_choice')->first();
        $this->assertNotNull($mcq);
        $this->assertEquals(2, $mcq->options()->count());
        $this->assertEquals(1, $mcq->options()->where('is_correct', true)->count());
    }

    public function test_quiz_export_service_exports_valid_csv_with_utf8_bom(): void
    {
        $service = app(QuizExportService::class);
        $csv = $service->exportToCsv(collect([$this->quiz]));

        // Assert UTF-8 BOM is present
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Assert Quiz and Question data
        $this->assertStringContainsString('Quiz ID', $csv);
        $this->assertStringContainsString('JavaScript Basics Quiz', $csv);
        $this->assertStringContainsString('WEB-101', $csv);
        $this->assertStringContainsString('What is 2 + 2 in JS?', $csv);
        $this->assertStringContainsString('Explain event bubbling in the DOM.', $csv);
        $this->assertStringContainsString('Write a function that reverses a string.', $csv);
        $this->assertStringContainsString('B) 4', $csv);
    }

    public function test_quiz_export_service_handles_quiz_without_questions(): void
    {
        $emptyQuiz = Quiz::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Empty Draft Quiz',
            'is_active' => false,
        ]);

        $service = app(QuizExportService::class);

        // JSON export
        $json = $service->exportToJson(collect([$emptyQuiz]));
        $data = json_decode($json, true);
        $this->assertCount(1, $data);
        $this->assertEquals('Empty Draft Quiz', $data[0]['title']);
        $this->assertEmpty($data[0]['questions']);

        // CSV export
        $csv = $service->exportToCsv(collect([$emptyQuiz]));
        $this->assertStringContainsString('Empty Draft Quiz', $csv);
        $this->assertStringContainsString('No questions configured', $csv);
    }

    public function test_quiz_export_service_generates_streamed_response(): void
    {
        $service = app(QuizExportService::class);

        // JSON response
        $jsonResponse = $service->downloadResponse(collect([$this->quiz]), 'json', 'custom_quiz.json');
        $this->assertEquals('application/json', $jsonResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('custom_quiz.json', $jsonResponse->headers->get('Content-Disposition'));

        // CSV response
        $csvResponse = $service->downloadResponse(collect([$this->quiz]), 'csv', 'custom_quiz.csv');
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('custom_quiz.csv', $csvResponse->headers->get('Content-Disposition'));
    }

    public function test_instructor_export_action_is_scoped_to_assigned_courses(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        // Course 1 assigned to instructor
        $course1 = Course::query()->create([
            'title' => 'Instructor Course',
            'code' => 'INS-101',
            'slug' => 'instructor-course',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        // Course 2 assigned to someone else
        $course2 = Course::query()->create([
            'title' => 'Other Course',
            'code' => 'OTH-202',
            'slug' => 'other-course',
            'course_by' => '999',
            'is_active' => true,
        ]);

        $quiz1 = Quiz::query()->create([
            'course_id' => $course1->id,
            'title' => 'Instructor Quiz',
            'is_active' => true,
        ]);

        $quiz2 = Quiz::query()->create([
            'course_id' => $course2->id,
            'title' => 'Other Quiz',
            'is_active' => true,
        ]);

        $action = ExportQuizzesAction::makeForCourses([$course1->id]);

        $this->actingAs($instructor);

        // Call the action with empty course_id (meaning export all assigned courses)
        $actionClosure = (new \ReflectionClass($action))->getProperty('action')->getValue($action);
        $response = $actionClosure(['format' => 'json', 'course_id' => null, 'only_active' => false]);

        $this->assertNotNull($response);

        // Capture streamed output
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $data = json_decode($content, true);
        $this->assertCount(1, $data);
        $this->assertEquals('Instructor Quiz', $data[0]['title']);
        $this->assertEquals('INS-101', $data[0]['course_code']);
    }

    public function test_instructor_cannot_export_unassigned_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $course1 = Course::query()->create([
            'title' => 'Course One',
            'code' => 'C-1',
            'slug' => 'course-one',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $course2 = Course::query()->create([
            'title' => 'Course Two',
            'code' => 'C-2',
            'slug' => 'course-two',
            'course_by' => '999',
            'is_active' => true,
        ]);

        $action = ExportQuizzesAction::makeForCourses([$course1->id]);

        $this->actingAs($instructor);

        // Try to export Course 2 (which is unauthorized for this instructor)
        $actionClosure = (new \ReflectionClass($action))->getProperty('action')->getValue($action);
        $response = $actionClosure(['format' => 'json', 'course_id' => $course2->id, 'only_active' => false]);

        $this->assertNull($response);
    }

    public function test_export_action_filters_by_only_active_flag(): void
    {
        $activeQuiz = Quiz::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Active Quiz',
            'is_active' => true,
        ]);

        $draftQuiz = Quiz::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Draft Inactive Quiz',
            'is_active' => false,
        ]);

        $action = ExportQuizzesAction::makeForCourses([$this->course->id]);

        $actionClosure = (new \ReflectionClass($action))->getProperty('action')->getValue($action);

        // only_active = true
        $response = $actionClosure(['format' => 'json', 'course_id' => $this->course->id, 'only_active' => true]);
        $this->assertNotNull($response);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $data = json_decode($content, true);
        $titles = collect($data)->pluck('title')->all();

        $this->assertContains('JavaScript Basics Quiz', $titles);
        $this->assertContains('Active Quiz', $titles);
        $this->assertNotContains('Draft Inactive Quiz', $titles);
    }
}
