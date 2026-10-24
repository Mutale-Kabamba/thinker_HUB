<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\InstructorOverview;
use App\Filament\Instructor\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Resources\InstructorApplications\Pages\EditInstructorApplication;
use App\Filament\Resources\InstructorApplications\Pages\ListInstructorApplications;
use App\Models\Course;
use App\Models\InstructorApplication;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstructorApplicationCourseAssignmentAndPromptTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $applicantUser;
    protected Course $existingCourseA;
    protected Course $existingCourseB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->applicantUser = User::factory()->create([
            'role' => 'student',
            'is_active' => false,
            'name' => 'John Instructor',
            'email' => 'john.instructor@thinkerhub.test',
        ]);

        $this->existingCourseA = Course::query()->create([
            'title' => 'Python for Data Science',
            'code' => 'PY-101',
            'is_active' => false,
        ]);

        $this->existingCourseB = Course::query()->create([
            'title' => 'Advanced Cloud Architecture',
            'code' => 'CLD-401',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_select_and_assign_existing_course_on_application_approval(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->admin);

        $application = InstructorApplication::query()->create([
            'user_id' => $this->applicantUser->id,
            'name' => $this->applicantUser->name,
            'email' => $this->applicantUser->email,
            'qualifications' => 'BSc Computer Science, 5 years teaching',
            'proposal_type' => 'new',
            'status' => 'pending',
        ]);

        Livewire::test(EditInstructorApplication::class, ['record' => $application->id])
            ->fillForm([
                'course_assignment_action' => 'assign_existing',
                'assigned_course_id' => $this->existingCourseA->id,
                'status' => 'approved',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $application->refresh();
        $this->applicantUser->refresh();
        $this->existingCourseA->refresh();

        $this->assertSame('approved', $application->status);
        $this->assertSame('assign_existing', $application->course_assignment_action);
        $this->assertSame($this->existingCourseA->id, $application->assigned_course_id);

        // Instructor activated and course linked
        $this->assertSame('instructor', $this->applicantUser->role);
        $this->assertTrue($this->applicantUser->is_active);
        $this->assertTrue($this->applicantUser->instructorCourses()->where('courses.id', $this->existingCourseA->id)->exists());
        $this->assertTrue($this->existingCourseA->is_active);
    }

    public function test_admin_can_use_assign_existing_course_header_action(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->admin);

        $application = InstructorApplication::query()->create([
            'user_id' => $this->applicantUser->id,
            'name' => $this->applicantUser->name,
            'email' => $this->applicantUser->email,
            'qualifications' => 'MSc Software Engineering',
            'proposal_type' => 'existing',
            'status' => 'pending',
        ]);

        Livewire::test(EditInstructorApplication::class, ['record' => $application->id])
            ->callAction('assign_existing_course', [
                'course_id' => $this->existingCourseB->id,
                'approve_now' => true,
            ])
            ->assertHasNoActionErrors();

        $application->refresh();
        $this->applicantUser->refresh();
        $this->existingCourseB->refresh();

        $this->assertSame('approved', $application->status);
        $this->assertSame('assign_existing', $application->course_assignment_action);
        $this->assertSame($this->existingCourseB->id, $application->assigned_course_id);
        $this->assertTrue($this->applicantUser->instructorCourses()->where('courses.id', $this->existingCourseB->id)->exists());
        $this->assertTrue($this->existingCourseB->is_active);
    }

    public function test_admin_can_prompt_new_instructor_to_create_course_with_guide(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->admin);

        $application = InstructorApplication::query()->create([
            'user_id' => $this->applicantUser->id,
            'name' => $this->applicantUser->name,
            'email' => $this->applicantUser->email,
            'qualifications' => 'Certified Cloud DevOps Professional',
            'proposal_type' => 'new',
            'status' => 'pending',
        ]);

        Livewire::test(EditInstructorApplication::class, ['record' => $application->id])
            ->callAction('prompt_create_course', [
                'course_creation_guidance' => 'Focus on Kubernetes, CI/CD with GitHub Actions, and Terraform.',
                'approve_now' => true,
            ])
            ->assertHasNoActionErrors();

        $application->refresh();
        $this->applicantUser->refresh();

        $this->assertSame('approved', $application->status);
        $this->assertSame('prompt_create_course', $application->course_assignment_action);
        $this->assertTrue($application->prompt_course_creation);
        $this->assertNotNull($application->prompted_at);
        $this->assertSame('Focus on Kubernetes, CI/CD with GitHub Actions, and Terraform.', $application->course_creation_guidance);

        // Instructor account is activated
        $this->assertSame('instructor', $this->applicantUser->role);
        $this->assertTrue($this->applicantUser->is_active);

        // Database notification was delivered to instructor
        $this->assertTrue($this->applicantUser->notifications()->exists());
        $notification = $this->applicantUser->notifications()->latest()->first();
        $this->assertStringContainsString('Course Creation Request', $notification->data['title'] ?? '');
    }

    public function test_instructor_overview_displays_course_creation_prompt_banner_and_guide(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        // Setup instructor with active course creation prompt
        $this->applicantUser->update([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        InstructorApplication::query()->create([
            'user_id' => $this->applicantUser->id,
            'name' => $this->applicantUser->name,
            'email' => $this->applicantUser->email,
            'qualifications' => 'DevOps Instructor',
            'status' => 'approved',
            'prompt_course_creation' => true,
            'course_creation_guidance' => 'Please follow our 8-step structure guide carefully.',
            'prompted_at' => now(),
        ]);

        $this->actingAs($this->applicantUser);

        Livewire::test(InstructorOverview::class)
            ->assertSet('courseCreationPrompt.has_prompt', true)
            ->assertSet('courseCreationPrompt.guidance', 'Please follow our 8-step structure guide carefully.')
            ->assertSee('Course Creation Request')
            ->assertSee('Please follow our 8-step structure guide carefully.')
            ->assertSee('View Structure Guide')
            ->assertSee('Create Course Now');
    }

    public function test_creating_course_fulfills_and_clears_the_creation_prompt(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $this->applicantUser->update([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $application = InstructorApplication::query()->create([
            'user_id' => $this->applicantUser->id,
            'name' => $this->applicantUser->name,
            'email' => $this->applicantUser->email,
            'qualifications' => 'DevOps Instructor',
            'status' => 'approved',
            'prompt_course_creation' => true,
            'prompted_at' => now(),
        ]);

        $this->actingAs($this->applicantUser);

        // Submit course through instructor CreateCourse page
        Livewire::test(CreateCourse::class)
            ->fillForm([
                'title' => 'Kubernetes & Docker in Production',
                'code' => 'K8S-301',
                'description' => 'Comprehensive containerization course.',
                'timeline' => '6 Weeks',
                'offering_mode' => 'once_off',
                'fees' => [
                    [
                        'category' => 'one_on_one',
                        'level' => 'Beginner',
                        'amount' => '450',
                    ],
                ],
                'requirements' => "Laptop with 8GB RAM\nDocker installed",
                'level_progression' => [
                    [
                        'level' => 'Beginner',
                        'details' => 'Container fundamentals and Docker CLI',
                    ],
                ],
                'key_outcome' => 'Deploy production-grade microservices to Kubernetes.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $application->refresh();

        // Verify the prompt has been fulfilled and cleared
        $this->assertFalse($application->prompt_course_creation);

        $this->assertDatabaseHas('courses', [
            'title' => 'Kubernetes & Docker in Production',
            'code' => 'K8S-301',
        ]);
    }

    public function test_applications_table_has_assign_and_prompt_actions(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->admin);

        $application = InstructorApplication::query()->create([
            'user_id' => $this->applicantUser->id,
            'name' => $this->applicantUser->name,
            'email' => $this->applicantUser->email,
            'qualifications' => 'Certified Scrum Master',
            'status' => 'pending',
        ]);

        Livewire::test(ListInstructorApplications::class)
            ->assertTableActionExists('assign_course')
            ->assertTableActionExists('prompt_course');
    }
}
