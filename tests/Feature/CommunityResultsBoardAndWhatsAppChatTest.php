<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Community;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommunityResultsBoardAndWhatsAppChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('student'));
    }

    public function test_results_board_aggregates_quizzes_assignments_and_assessments(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $course = Course::query()->create(['title' => 'Computer Science 101', 'code' => 'CS-101', 'is_active' => true]);

        // 1. Completed Quiz
        $quiz = Quiz::query()->create([
            'course_id' => $course->id,
            'title' => 'Python Basics Quiz',
            'is_published' => true,
        ]);
        QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 18,
            'total_points' => 20,
            'percentage' => 90,
            'passed' => true,
            'completed_at' => now()->subDays(2),
        ]);

        // 2. Graded Assignment
        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'name' => 'Data Structures Project',
        ]);
        AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'grade' => 85,
            'feedback' => 'Well documented and clean implementation.',
            'submitted_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // 3. Graded Assessment
        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'name' => 'Midterm Technical Assessment',
        ]);
        AssessmentSubmission::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'score' => 95,
            'feedback' => 'Exceptional performance on recursion problems.',
            'submitted_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student);

        Livewire::test(Community::class)
            ->set('tab', 'results')
            ->assertSee('Score Board')
            ->assertSee('Python Basics Quiz')
            ->assertSee('Data Structures Project')
            ->assertSee('Midterm Technical Assessment')
            ->assertSee('90%')
            ->assertSee('85%')
            ->assertSee('95%')
            ->assertSee('Exceptional performance on recursion problems.')
            ->assertSee('Well documented and clean implementation.')
            // Verify real-time search filtering
            ->set('resultsSearch', 'Python')
            ->assertSee('Python Basics Quiz')
            ->assertDontSee('Data Structures Project')
            ->assertDontSee('Midterm Technical Assessment')
            // Verify type filtering
            ->set('resultsSearch', '')
            ->set('resultsFilter', 'assignment')
            ->assertSee('Data Structures Project')
            ->assertDontSee('Python Basics Quiz')
            ->assertDontSee('Midterm Technical Assessment');
    }

    public function test_whatsapp_chat_room_selection_and_close_room(): void
    {
        $student = User::factory()->create(['name' => 'John Doe', 'role' => 'student', 'is_active' => true]);
        $course = Course::query()->create(['title' => 'Robotics 101', 'code' => 'ROB-101', 'is_active' => true]);
        $student->enrollments()->create(['course_id' => $course->id, 'status' => 'enrolled']);

        $this->actingAs($student);

        $component = Livewire::test(Community::class)
            ->set('tab', 'chats')
            ->assertSee('Community Chats')
            ->assertSee('ROB-101');

        $rooms = $component->get('rooms');
        $this->assertNotEmpty($rooms);
        $roomId = $rooms->first()->id;

        $component->call('selectRoom', $roomId)
            ->assertSet('selectedRoomId', $roomId)
            ->call('closeRoom')
            ->assertSet('selectedRoomId', null);
    }

    public function test_results_board_shows_student_performance_leaderboard(): void
    {
        $topStudent = User::factory()->create(['name' => 'Alice Top', 'role' => 'student', 'is_active' => true]);
        $secondStudent = User::factory()->create(['name' => 'Bob Second', 'role' => 'student', 'is_active' => true]);
        $course = Course::query()->create(['title' => 'Math 101', 'code' => 'MTH-101', 'is_active' => true]);

        $quiz = Quiz::query()->create(['course_id' => $course->id, 'title' => 'Calculus Quiz', 'is_published' => true]);
        QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $topStudent->id,
            'score' => 20,
            'total_points' => 20,
            'percentage' => 100,
            'passed' => true,
            'completed_at' => now(),
        ]);

        QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $secondStudent->id,
            'score' => 15,
            'total_points' => 20,
            'percentage' => 75,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $this->actingAs($secondStudent);

        Livewire::test(Community::class)
            ->set('tab', 'results')
            ->assertSee('Score Board')
            ->assertSee('Recent Graded Tasks')
            ->assertSee('Calculus Quiz')
            ->assertSee('Alice Top')
            ->assertSee('Bob Second')
            ->assertSee('100%')
            ->assertSee('75%')
            ->assertDontSee('BREAKDOWN')
            ->assertDontSee('Evaluation types');
    }
}

