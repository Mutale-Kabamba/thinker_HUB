<?php

namespace Tests\Feature;

use App\Ai\Agents\QuizGeneratorAgent;
use App\Filament\Actions\GenerateAiQuizAction;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Prompts\AgentPrompt;
use Tests\TestCase;

class GenerateAiQuizActionTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private CourseIntake $intake;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::query()->create([
            'title' => 'Modern PHP & Laravel',
            'code' => 'LAR-201',
            'is_active' => true,
        ]);

        $this->intake = CourseIntake::query()->create([
            'course_id' => $this->course->id,
            'name' => 'Cohort 2026',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_generate_quiz_via_ai_agent(): void
    {
        QuizGeneratorAgent::fake([
            [
                'title' => 'Laravel Service Container Mastery',
                'description' => 'A comprehensive assessment on inversion of control and dependency injection.',
                'questions' => [
                    [
                        'type' => 'multiple_choice',
                        'question' => 'Which method is used to bind a singleton into the service container?',
                        'explanation' => 'app()->singleton() registers a shared instance.',
                        'points' => 2,
                        'options' => [
                            ['option_text' => '$app->singleton()', 'is_correct' => true],
                            ['option_text' => '$app->bindOnce()', 'is_correct' => false],
                            ['option_text' => '$app->share()', 'is_correct' => false],
                            ['option_text' => '$app->instanceAlways()', 'is_correct' => false],
                        ],
                    ],
                    [
                        'type' => 'theory',
                        'question' => 'Explain contextual binding in Laravel.',
                        'explanation' => 'Contextual binding allows different implementations of an interface depending on the consuming class.',
                        'points' => 5,
                        'options' => null,
                    ],
                    [
                        'type' => 'practical',
                        'question' => 'Write a service provider register method binding PaymentGatewayInterface to StripePaymentGateway.',
                        'explanation' => 'public function register(): void { $this->app->bind(PaymentGatewayInterface::class, StripePaymentGateway::class); }',
                        'points' => 5,
                        'options' => null,
                    ],
                ],
            ],
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $action = GenerateAiQuizAction::make();

        $action->call([
            'data' => [
                'course_id' => $this->course->id,
                'course_intake_id' => $this->intake->id,
                'topic' => 'Service Container & Dependency Injection',
                'question_count' => 3,
                'question_types' => ['multiple_choice', 'theory', 'practical'],
                'is_active' => true,
            ],
        ]);

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'title' => 'Laravel Service Container Mastery',
            'is_active' => true,
        ]);

        $quiz = Quiz::where('course_id', $this->course->id)->firstOrFail();
        $this->assertCount(3, $quiz->questions);

        $mcq = $quiz->questions()->where('type', 'multiple_choice')->firstOrFail();
        $this->assertCount(4, $mcq->options);
        $this->assertEquals(1, $mcq->options()->where('is_correct', true)->count());

        $theory = $quiz->questions()->where('type', 'theory')->firstOrFail();
        $this->assertEquals(5, $theory->points);

        $practical = $quiz->questions()->where('type', 'practical')->firstOrFail();
        $this->assertEquals(5, $practical->points);

        QuizGeneratorAgent::assertPrompted(function (AgentPrompt $prompt): bool {
            return str_contains($prompt->prompt, 'Modern PHP & Laravel')
                && str_contains($prompt->prompt, 'Service Container');
        });
    }

    public function test_generate_quiz_attaches_learning_material_document(): void
    {
        Storage::fake('public');
        $pdfPath = 'materials/syllabus_test.pdf';
        Storage::disk('public')->put($pdfPath, '%PDF-1.4 sample content');

        $material = LearningMaterial::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Laravel Syllabus PDF',
            'category' => LearningMaterial::CATEGORY_CURRICULUM,
            'file_path' => $pdfPath,
        ]);

        QuizGeneratorAgent::fake([
            [
                'title' => 'Syllabus Grounded Quiz',
                'description' => 'Quiz generated directly from course reading material.',
                'questions' => [
                    [
                        'type' => 'theory',
                        'question' => 'Summarize the core topics covered in week 1.',
                        'explanation' => 'Must mention routing, middleware, and controllers.',
                        'points' => 5,
                        'options' => null,
                    ],
                ],
            ],
        ]);

        $action = GenerateAiQuizAction::make();

        $action->call([
            'data' => [
                'course_id' => $this->course->id,
                'learning_material_id' => $material->id,
                'topic' => 'Week 1 Foundations',
                'question_count' => 1,
                'question_types' => ['theory'],
                'is_active' => false,
            ],
        ]);

        $quiz = Quiz::where('title', 'Syllabus Grounded Quiz')->first();
        $this->assertNotNull($quiz);
        $this->assertFalse((bool) $quiz->is_active);

        QuizGeneratorAgent::assertPrompted(function (AgentPrompt $prompt): bool {
            return $prompt->attachments->isNotEmpty();
        });
    }

    public function test_instructor_action_rejects_unassigned_course(): void
    {
        $otherCourse = Course::query()->create([
            'title' => 'Other Instructor Course',
            'code' => 'OTH-999',
            'is_active' => true,
        ]);

        QuizGeneratorAgent::fake();

        // Action scoped ONLY to $this->course->id
        $action = GenerateAiQuizAction::makeForCourses([$this->course->id]);

        $action->call([
            'data' => [
                'course_id' => $otherCourse->id,
                'topic' => 'Unauthorized Attempt',
                'question_count' => 5,
                'question_types' => ['theory'],
                'is_active' => false,
            ],
        ]);

        $this->assertDatabaseMissing('quizzes', [
            'course_id' => $otherCourse->id,
        ]);

        QuizGeneratorAgent::assertNeverPrompted();
    }

    public function test_instructor_action_generates_quiz_for_assigned_course(): void
    {
        QuizGeneratorAgent::fake([
            [
                'title' => 'Scoped Instructor Quiz',
                'description' => 'Assessment authored by instructor.',
                'questions' => [
                    [
                        'type' => 'multiple_choice',
                        'question' => 'What is Livewire?',
                        'explanation' => 'Full-stack framework for Laravel.',
                        'points' => 1,
                        'options' => [
                            ['option_text' => 'Full-stack framework for Laravel', 'is_correct' => true],
                            ['option_text' => 'CSS Framework', 'is_correct' => false],
                        ],
                    ],
                ],
            ],
        ]);

        $action = GenerateAiQuizAction::makeForCourses([$this->course->id]);

        $action->call([
            'data' => [
                'course_id' => $this->course->id,
                'topic' => 'Livewire Components',
                'question_count' => 1,
                'question_types' => ['multiple_choice'],
                'is_active' => false,
            ],
        ]);

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $this->course->id,
            'title' => 'Scoped Instructor Quiz',
        ]);

        QuizGeneratorAgent::assertPromptedTimes(1);
    }
}

