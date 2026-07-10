<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\StudentResource\Pages\CreateStudent as InstructorCreateStudent;
use App\Filament\Resources\Students\Pages\CreateStudent as AdminCreateStudent;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class StudentCreationVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_student_create_flow_marks_student_as_verified(): void
    {
        $data = [
            'name' => 'Learner One',
            'email' => 'learner1@example.com',
            'password' => 'password',
        ];

        $mutated = $this->mutateCreateData(new InstructorCreateStudent(), $data);

        $this->assertSame('student', $mutated['role']);
        $this->assertArrayHasKey('email_verified_at', $mutated);
        $this->assertNotNull($mutated['email_verified_at']);
    }

    public function test_admin_student_create_flow_marks_student_as_verified(): void
    {
        $data = [
            'name' => 'Learner Two',
            'email' => 'learner2@example.com',
            'password' => 'password',
        ];

        $mutated = $this->mutateCreateData(new AdminCreateStudent(), $data);

        $this->assertSame('student', $mutated['role']);
        $this->assertArrayHasKey('email_verified_at', $mutated);
        $this->assertNotNull($mutated['email_verified_at']);
    }

    public function test_admin_user_create_flow_marks_only_students_as_verified(): void
    {
        $studentData = [
            'name' => 'Learner Three',
            'email' => 'learner3@example.com',
            'password' => 'password',
            'role' => 'student',
            'track' => 'Beginner',
        ];

        $mutatedStudent = $this->mutateCreateData(new CreateUser(), $studentData);

        $this->assertArrayHasKey('email_verified_at', $mutatedStudent);
        $this->assertNotNull($mutatedStudent['email_verified_at']);

        $instructorData = [
            'name' => 'Instructor One',
            'email' => 'instructor1@example.com',
            'password' => 'password',
            'role' => 'instructor',
            'track' => 'Advanced',
        ];

        $mutatedInstructor = $this->mutateCreateData(new CreateUser(), $instructorData);

        $this->assertArrayNotHasKey('email_verified_at', $mutatedInstructor);
        $this->assertNull($mutatedInstructor['track']);
    }

    public function test_public_registration_still_creates_unverified_student(): void
    {
        $course = Course::query()->create([
            'title' => 'Data Foundations',
            'code' => 'DF-101',
            'description' => 'Introductory course',
            'is_active' => true,
        ]);

        $this->post('/register', [
            'name' => 'Public Student',
            'email' => 'public.student@example.com',
            'course_id' => $course->id,
            'track' => 'Beginner',
            'accept_terms' => true,
            'accept_requirements' => true,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'public.student@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        $this->assertSame('student', $user->role);
    }

    private function mutateCreateData(object $page, array $data): array
    {
        $method = new ReflectionMethod($page, 'mutateFormDataBeforeCreate');
        $method->setAccessible(true);

        /** @var array<string, mixed> $mutated */
        $mutated = $method->invoke($page, $data);

        return $mutated;
    }
}
