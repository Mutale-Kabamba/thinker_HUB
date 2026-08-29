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

    public function test_multi_course_student_accumulates_points_separately_per_course(): void
    {
        $courseA = Course::create([
            'title' => 'Web Development',
            'code' => 'WEB-100',
            'is_active' => true,
        ]);

        $intakeA = CourseIntake::create([
            'course_id' => $courseA->id,
            'name' => 'Intake 1',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        $courseB = Course::create([
            'title' => 'Graphic Design',
            'code' => 'DES-200',
            'is_active' => true,
        ]);

        $intakeB = CourseIntake::create([
            'course_id' => $courseB->id,
            'name' => 'Intake 2',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'is_active' => true,
        ]);

        // Student enrolled in both courses
        $studentMulti = User::factory()->create(['name' => 'Dual Student', 'role' => 'student', 'is_active' => true]);
        Enrollment::create(['user_id' => $studentMulti->id, 'course_id' => $courseA->id, 'course_intake_id' => $intakeA->id]);
        Enrollment::create(['user_id' => $studentMulti->id, 'course_id' => $courseB->id, 'course_intake_id' => $intakeB->id]);

        // Peer in Course A only
        $peerA = User::factory()->create(['name' => 'Web Peer', 'role' => 'student', 'is_active' => true]);
        Enrollment::create(['user_id' => $peerA->id, 'course_id' => $courseA->id, 'course_intake_id' => $intakeA->id]);

        // Peer in Course B only
        $peerB = User::factory()->create(['name' => 'Design Peer', 'role' => 'student', 'is_active' => true]);
        Enrollment::create(['user_id' => $peerB->id, 'course_id' => $courseB->id, 'course_intake_id' => $intakeB->id]);

        $quizA = Quiz::create(['course_id' => $courseA->id, 'course_intake_id' => $intakeA->id, 'title' => 'Web Quiz', 'is_active' => true]);
        $quizB = Quiz::create(['course_id' => $courseB->id, 'course_intake_id' => $intakeB->id, 'title' => 'Design Quiz', 'is_active' => true]);

        $service = app(GamificationService::class);

        // Award 300 XP in Web Quiz and 500 XP in Design Quiz
        $service->awardPoints($studentMulti, 'quiz_passed', $quizA, 300, 10, 'Quiz completed');
        $service->awardPoints($studentMulti, 'quiz_passed', $quizB, 500, 15, 'Quiz completed');

        // Award 400 XP to Web Peer in Web Quiz
        $service->awardPoints($peerA, 'quiz_passed', $quizA, 400, 10, 'Quiz completed');

        // Award 200 XP to Design Peer in Design Quiz
        $service->awardPoints($peerB, 'quiz_passed', $quizB, 200, 10, 'Quiz completed');

        $studentMulti->refresh();
        $peerA->refresh();
        $peerB->refresh();

        // Check course specific XP
        $this->assertEquals(300, $studentMulti->getXpForCourse($courseA->id, $intakeA->id));
        $this->assertEquals(500, $studentMulti->getXpForCourse($courseB->id, $intakeB->id));
        // Total lifetime XP remains aggregate
        $this->assertEquals(800, (int) $studentMulti->lifetime_xp);

        // Web Peer checks leaderboard: Web Peer (400 XP) is #1, Dual Student (300 XP) is #2, Design Peer is not present
        $webLeaderboard = $service->leaderboard($peerA);
        $this->assertEquals(2, $webLeaderboard->count());
        $this->assertEquals($peerA->id, $webLeaderboard[0]['user_id']);
        $this->assertEquals(400, $webLeaderboard[0]['xp']);
        $this->assertEquals($studentMulti->id, $webLeaderboard[1]['user_id']);
        $this->assertEquals(300, $webLeaderboard[1]['xp']);

        // Design Peer checks leaderboard: Dual Student (500 XP) is #1, Design Peer (200 XP) is #2, Web Peer is not present
        $designLeaderboard = $service->leaderboard($peerB);
        $this->assertEquals(2, $designLeaderboard->count());
        $this->assertEquals($studentMulti->id, $designLeaderboard[0]['user_id']);
        $this->assertEquals(500, $designLeaderboard[0]['xp']);
        $this->assertEquals($peerB->id, $designLeaderboard[1]['user_id']);
        $this->assertEquals(200, $designLeaderboard[1]['xp']);
    }

    public function test_leaderboard_switcher_and_friends_list_points(): void
    {
        $courseA = Course::create(['title' => 'Fullstack Dev', 'code' => 'FS-100', 'is_active' => true]);
        $intakeA = CourseIntake::create(['course_id' => $courseA->id, 'name' => 'Cohort 1', 'start_date' => now()->subMonth(), 'end_date' => now()->addMonths(2), 'status' => 'active', 'is_active' => true]);

        $courseB = Course::create(['title' => 'UI UX Design', 'code' => 'UX-200', 'is_active' => true]);
        $intakeB = CourseIntake::create(['course_id' => $courseB->id, 'name' => 'Cohort 2', 'start_date' => now()->subMonth(), 'end_date' => now()->addMonths(2), 'status' => 'active', 'is_active' => true]);

        $student = User::factory()->create(['name' => 'Alex Multi', 'role' => 'student', 'is_active' => true]);
        $friend = User::factory()->create(['name' => 'Taylor Friend', 'role' => 'student', 'is_active' => true]);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $courseA->id, 'course_intake_id' => $intakeA->id]);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $courseB->id, 'course_intake_id' => $intakeB->id]);

        Enrollment::create(['user_id' => $friend->id, 'course_id' => $courseA->id, 'course_intake_id' => $intakeA->id]);
        Enrollment::create(['user_id' => $friend->id, 'course_id' => $courseB->id, 'course_intake_id' => $intakeB->id]);

        Friendship::create(['user_id' => $student->id, 'friend_id' => $friend->id, 'status' => 'accepted']);

        $service = app(GamificationService::class);
        $service->awardPoints($friend, 'quiz_passed', null, 150, 5, 'Fullstack Passed', $courseA->id, $intakeA->id);
        $service->awardPoints($friend, 'quiz_passed', null, 350, 10, 'UX Passed', $courseB->id, $intakeB->id);

        $service->awardPoints($student, 'quiz_passed', null, 500, 15, 'Fullstack Passed', $courseA->id, $intakeA->id);
        $service->awardPoints($student, 'quiz_passed', null, 100, 5, 'UX Passed', $courseB->id, $intakeB->id);

        // Friends tab shows both badges with their distinct XP
        Livewire::actingAs($student)
            ->test(Community::class)
            ->set('tab', 'friends')
            ->assertSee('Taylor Friend')
            ->assertSee('FS-100 • Cohort 1')
            ->assertSee('(165 XP)')
            ->assertSee('UX-200 • Cohort 2')
            ->assertSee('(350 XP)');

        // Leaderboard tab has class switcher and switches rankings
        Livewire::actingAs($student)
            ->test(Community::class)
            ->set('tab', 'leaderboard')
            // Default to first class (FS-100) -> Alex Multi #1 with 500 XP
            ->assertSee('Leaderboard')
            ->assertSee('FS-100 • Cohort 1')
            ->assertSee('500')
            // Switch to UX-200 -> Taylor Friend is #1 with 350 XP, Alex Multi has 100 XP
            ->call('selectLeaderboardClass', $courseB->id, $intakeB->id)
            ->assertSee('UX-200 • Cohort 2')
            ->assertSee('350')
            ->assertSee('100');
    }
}
