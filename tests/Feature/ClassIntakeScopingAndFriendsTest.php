<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Community;
use App\Filament\Student\Pages\Quizzes;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\Friendship;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClassIntakeScopingAndFriendsTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_scopes_to_students_in_same_course_and_intake(): void
    {
        $courseA = Course::create([
            'title' => 'Web Development Bootcamp',
            'code' => 'WEB-101',
            'is_active' => true,
        ]);

        $intake1 = CourseIntake::create([
            'course_id' => $courseA->id,
            'name' => 'Cohort Alpha',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $intake2 = CourseIntake::create([
            'course_id' => $courseA->id,
            'name' => 'Cohort Beta',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $courseB = Course::create([
            'title' => 'Graphic Design Masterclass',
            'code' => 'DES-201',
            'is_active' => true,
        ]);

        $student1 = User::factory()->create([
            'name' => 'Alice Alpha',
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 1500,
        ]);
        $student2 = User::factory()->create([
            'name' => 'Bob Alpha',
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 2000,
        ]);
        $student3 = User::factory()->create([
            'name' => 'Charlie Beta',
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 3000,
        ]);
        $student4 = User::factory()->create([
            'name' => 'David Design',
            'role' => 'student',
            'is_active' => true,
            'lifetime_xp' => 5000,
        ]);

        Enrollment::create(['user_id' => $student1->id, 'course_id' => $courseA->id, 'course_intake_id' => $intake1->id]);
        Enrollment::create(['user_id' => $student2->id, 'course_id' => $courseA->id, 'course_intake_id' => $intake1->id]);
        Enrollment::create(['user_id' => $student3->id, 'course_id' => $courseA->id, 'course_intake_id' => $intake2->id]);
        Enrollment::create(['user_id' => $student4->id, 'course_id' => $courseB->id, 'course_intake_id' => null]);

        $service = app(GamificationService::class);

        // Student 1 (Cohort Alpha) only sees Student 1 and Student 2
        $leaderboardStudent1 = $service->leaderboard($student1);
        $this->assertEquals(2, $leaderboardStudent1->count());
        $this->assertEquals([$student2->id, $student1->id], $leaderboardStudent1->pluck('user_id')->all());

        // Livewire Community Leaderboard tab test
        Livewire::actingAs($student1)
            ->test(Community::class)
            ->set('tab', 'leaderboard')
            ->assertSee('Bob Alpha')
            ->assertSee('Alice Alpha')
            ->assertDontSee('Charlie Beta')
            ->assertDontSee('David Design');
    }

    public function test_score_board_results_and_tasks_scope_to_class_peers_and_enrolled_content(): void
    {
        $course = Course::create([
            'title' => 'Python Analytics',
            'code' => 'PY-100',
            'is_active' => true,
        ]);

        $intake1 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Class One',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $intake2 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Class Two',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $student1 = User::factory()->create(['name' => 'John Class1', 'role' => 'student', 'is_active' => true]);
        $student2 = User::factory()->create(['name' => 'Jane Class1', 'role' => 'student', 'is_active' => true]);
        $student3 = User::factory()->create(['name' => 'Mike Class2', 'role' => 'student', 'is_active' => true]);

        Enrollment::create(['user_id' => $student1->id, 'course_id' => $course->id, 'course_intake_id' => $intake1->id]);
        Enrollment::create(['user_id' => $student2->id, 'course_id' => $course->id, 'course_intake_id' => $intake1->id]);
        Enrollment::create(['user_id' => $student3->id, 'course_id' => $course->id, 'course_intake_id' => $intake2->id]);

        $assignment1 = Assignment::create([
            'course_id' => $course->id,
            'course_intake_id' => $intake1->id,
            'name' => 'Pandas Data Cleaning',
            'target_level' => null,
            'target_track' => null,
            'target_user_id' => null,
        ]);

        $assignment2 = Assignment::create([
            'course_id' => $course->id,
            'course_intake_id' => $intake2->id,
            'name' => 'Numpy Matrix Operations',
            'target_level' => null,
            'target_track' => null,
            'target_user_id' => null,
        ]);

        // Student 1 & 2 submit to assignment 1
        AssignmentSubmission::create(['assignment_id' => $assignment1->id, 'user_id' => $student1->id, 'grade' => 88.0, 'status' => 'graded']);
        AssignmentSubmission::create(['assignment_id' => $assignment1->id, 'user_id' => $student2->id, 'grade' => 95.0, 'status' => 'graded']);

        // Student 3 submits to assignment 2
        AssignmentSubmission::create(['assignment_id' => $assignment2->id, 'user_id' => $student3->id, 'grade' => 99.0, 'status' => 'graded']);

        Livewire::actingAs($student1)
            ->test(Community::class)
            ->set('tab', 'results')
            ->assertSee('John Class1')
            ->assertSee('Jane Class1')
            ->assertDontSee('Mike Class2')
            ->assertSee('Pandas Data Cleaning')
            ->assertDontSee('Numpy Matrix Operations');
    }

    public function test_student_quizzes_page_scopes_to_enrolled_intake(): void
    {
        $course = Course::create([
            'title' => 'Database Design',
            'code' => 'DB-101',
            'is_active' => true,
        ]);

        $intake1 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort 1',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $intake2 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort 2',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['name' => 'Sam Student', 'role' => 'student', 'is_active' => true]);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'course_intake_id' => $intake1->id]);

        $quiz1 = Quiz::create([
            'course_id' => $course->id,
            'course_intake_id' => $intake1->id,
            'title' => 'Cohort 1 SQL Normalization Quiz',
            'is_active' => true,
        ]);

        $quiz2 = Quiz::create([
            'course_id' => $course->id,
            'course_intake_id' => $intake2->id,
            'title' => 'Cohort 2 Advanced Indexing Quiz',
            'is_active' => true,
        ]);

        Livewire::actingAs($student)
            ->test(Quizzes::class)
            ->assertSee('Cohort 1 SQL Normalization Quiz')
            ->assertDontSee('Cohort 2 Advanced Indexing Quiz');
    }

    public function test_friends_tab_shows_all_students_with_course_and_intake_labels(): void
    {
        $course = Course::create([
            'title' => 'Mobile App Development',
            'code' => 'MOB-501',
            'is_active' => true,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Spring Intake',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $studentA = User::factory()->create(['name' => 'Student Alpha', 'role' => 'student', 'is_active' => true]);
        $studentB = User::factory()->create(['name' => 'Student Bravo', 'role' => 'student', 'is_active' => true]);

        Enrollment::create(['user_id' => $studentA->id, 'course_id' => $course->id, 'course_intake_id' => $intake->id]);
        Enrollment::create(['user_id' => $studentB->id, 'course_id' => $course->id, 'course_intake_id' => $intake->id]);

        Friendship::create([
            'user_id' => $studentA->id,
            'friend_id' => $studentB->id,
            'status' => 'accepted',
        ]);

        Livewire::actingAs($studentA)
            ->test(Community::class)
            ->set('tab', 'friends')
            ->assertSee('Student Bravo')
            ->assertSee('MOB-501 • Spring Intake');
    }
}
