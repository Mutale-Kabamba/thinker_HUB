<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\ListCourseIntakes as InstructorListCourseIntakes;
use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\ViewCourseIntake as InstructorViewCourseIntake;
use App\Filament\Resources\CourseIntakes\Pages\CreateCourseIntake;
use App\Filament\Resources\CourseIntakes\Pages\EditCourseIntake;
use App\Filament\Resources\CourseIntakes\Pages\ListCourseIntakes;
use App\Filament\Resources\CourseIntakes\Pages\ViewCourseIntake;
use App\Filament\Resources\CourseIntakes\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\AssessmentsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\MaterialsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\QuizzesRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\SessionsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\CourseIntakes\RelationManagers\VideosRelationManager;
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
use Livewire\Livewire;
use Tests\TestCase;

class CourseIntakeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_manage_course_intakes_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $course = Course::create([
            'title' => 'Advanced Robotics',
            'code' => 'ROB-500',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Spring 2026 Batch',
            'start_date' => '2026-03-01',
            'end_date' => '2026-06-01',
            'status' => CourseIntake::STATUS_UPCOMING,
            'is_active' => false,
        ]);

        // List renders course title
        Livewire::test(ListCourseIntakes::class)
            ->assertSuccessful()
            ->assertSee('Spring 2026 Batch')
            ->assertSee('Advanced Robotics');

        // Test creating an intake via Admin Resource
        Livewire::test(CreateCourseIntake::class)
            ->fillForm([
                'course_id' => $course->id,
                'name' => 'Autumn 2026 Batch',
                'start_date' => '2026-09-01',
                'end_date' => '2026-12-01',
                'status' => CourseIntake::STATUS_ACTIVE,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(CourseIntake::query()->where('name', 'Autumn 2026 Batch')->exists());
        $created = CourseIntake::query()->where('name', 'Autumn 2026 Batch')->first();
        $this->assertTrue($created->is_active);

        // Test editing
        Livewire::test(EditCourseIntake::class, ['record' => $created->id])
            ->fillForm([
                'name' => 'Autumn 2026 Batch (Updated)',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $created->refresh();
        $this->assertSame('Autumn 2026 Batch (Updated)', $created->name);
    }

    public function test_view_course_intake_page_renders_all_metrics_and_resources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $course = Course::create([
            'title' => 'Deep Learning Bootcamp',
            'code' => 'DL-800',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort 2026-A',
            'start_date' => '2026-02-01',
            'end_date' => '2026-05-01',
            'next_intake_start_date' => '2026-06-01',
            'max_capacity' => 25,
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'track' => 'Intermediate']);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'course_intake_id' => $intake->id]);

        Assignment::create(['course_id' => $course->id, 'course_intake_id' => $intake->id, 'name' => 'CNN Assignment', 'target_level' => 'Intermediate']);
        Assessment::create(['course_id' => $course->id, 'course_intake_id' => $intake->id, 'name' => 'Transformer Assessment', 'target_level' => 'Intermediate']);
        Quiz::create(['course_id' => $course->id, 'course_intake_id' => $intake->id, 'title' => 'Backprop Quiz', 'is_active' => true]);
        CourseSession::create(['course_id' => $course->id, 'course_intake_id' => $intake->id, 'title' => 'Live Lab 1', 'type' => 'group', 'session_date' => '2026-02-15', 'start_time' => '14:00', 'end_time' => '16:00']);
        LearningMaterial::create(['course_id' => $course->id, 'course_intake_id' => $intake->id, 'title' => 'Lecture Notes PDF', 'category' => 'Study Material', 'material_type' => 'Document', 'scope' => 'all']);
        ResourceVideo::create(['course_id' => $course->id, 'course_intake_id' => $intake->id, 'title' => 'Intro to Tensors', 'category' => 'Recorded Lessons', 'youtube_url' => 'https://youtube.com/watch?v=12345']);

        Livewire::test(ViewCourseIntake::class, ['record' => $intake->id])
            ->assertSuccessful()
            ->assertSee('Cohort Metrics & Overview')
            ->assertSee('Enrolled Students')
            ->assertSee('Assignments')
            ->assertSee('Assessments')
            ->assertSee('Quizzes')
            ->assertSee('Schedules / Sessions')
            ->assertSee('Materials')
            ->assertSee('Videos');
    }

    public function test_relation_managers_render_cohort_resources_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $course = Course::create([
            'title' => 'Cyber Forensics Masterclass',
            'code' => 'CYBER-400',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort Delta',
            'start_date' => '2026-03-01',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['name' => 'Alice Doe', 'email' => 'alice@test.com', 'role' => 'student']);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'course_intake_id' => $intake->id]);
        $assignment = Assignment::create(['course_id' => $course->id, 'course_intake_id' => $intake->id, 'name' => 'Forensic Artifacts', 'target_level' => 'Beginner']);

        Livewire::test(StudentsRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => ViewCourseIntake::class,
        ])
            ->assertSuccessful()
            ->assertSee('Alice Doe');

        Livewire::test(AssignmentsRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => ViewCourseIntake::class,
        ])
            ->assertSuccessful()
            ->assertSee('Forensic Artifacts');

        Livewire::test(AssessmentsRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => ViewCourseIntake::class,
        ])->assertSuccessful();

        Livewire::test(QuizzesRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => ViewCourseIntake::class,
        ])->assertSuccessful();

        Livewire::test(SessionsRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => ViewCourseIntake::class,
        ])->assertSuccessful();

        Livewire::test(MaterialsRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => ViewCourseIntake::class,
        ])->assertSuccessful();

        Livewire::test(VideosRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => ViewCourseIntake::class,
        ])->assertSuccessful();
    }

    public function test_instructor_sees_only_assigned_course_intakes_and_can_view(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $otherInstructor = User::factory()->create(['role' => 'instructor']);

        $myCourse = Course::create([
            'title' => 'My Assigned Course',
            'code' => 'MY-101',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);
        $myCourse->instructors()->attach($instructor->id);

        $otherCourse = Course::create([
            'title' => 'Other Course',
            'code' => 'OTHER-202',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);
        $otherCourse->instructors()->attach($otherInstructor->id);

        $myIntake = CourseIntake::create([
            'course_id' => $myCourse->id,
            'name' => 'My Course Intake Alpha',
            'start_date' => '2026-01-10',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $otherIntake = CourseIntake::create([
            'course_id' => $otherCourse->id,
            'name' => 'Other Course Intake Beta',
            'start_date' => '2026-01-10',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $this->actingAs($instructor);

        Livewire::test(InstructorListCourseIntakes::class)
            ->assertSuccessful()
            ->assertSee('My Course Intake Alpha')
            ->assertDontSee('Other Course Intake Beta');

        Livewire::test(InstructorViewCourseIntake::class, ['record' => $myIntake->id])
            ->assertSuccessful()
            ->assertSee('Cohort Metrics & Overview');
    }

    public function test_table_action_archive_and_launch_next_intake(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $course = Course::create([
            'title' => 'Full-Stack Architecture',
            'code' => 'FSA-101',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $currentIntake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort 2026-Q1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'next_intake_start_date' => '2026-04-15',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        Livewire::test(ListCourseIntakes::class)
            ->callTableAction('archive_and_launch_next', $currentIntake, [
                'next_name' => 'Cohort 2026-Q2',
                'next_start_date' => '2026-04-15',
                'next_end_date' => '2026-07-15',
                'subsequent_intake_start_date' => '2026-08-01',
            ])
            ->assertHasNoTableActionErrors();

        $currentIntake->refresh();
        $this->assertTrue($currentIntake->isArchived());
        $this->assertFalse($currentIntake->is_active);

        $newIntake = CourseIntake::query()->where('name', 'Cohort 2026-Q2')->first();
        $this->assertNotNull($newIntake);
        $this->assertTrue($newIntake->is_active);
        $this->assertSame(CourseIntake::STATUS_ACTIVE, $newIntake->status);
    }
}
