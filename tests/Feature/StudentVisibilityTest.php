<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_assignments_page_shows_only_visible_assignments(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $otherStudent = User::factory()->create([
            'role' => 'student',
            'track' => 'Advanced',
        ]);

        $enrolledCourse = Course::query()->create([
            'title' => 'Data Analysis Basics',
            'code' => 'DAB-101',
            'is_active' => true,
        ]);

        $hiddenCourse = Course::query()->create([
            'title' => 'Unenrolled Course',
            'code' => 'UNC-201',
            'is_active' => true,
        ]);

        $student->courses()->attach($enrolledCourse->id);

        Assignment::query()->create([
            'name' => 'Visible General Assignment',
            'course_id' => $enrolledCourse->id,
            'scope' => 'all',
        ]);

        Assignment::query()->create([
            'name' => 'Visible Beginner Assignment',
            'course_id' => $enrolledCourse->id,
            'scope' => 'level',
            'target_track' => 'Beginner',
        ]);

        Assignment::query()->create([
            'name' => 'Hidden Advanced Assignment',
            'course_id' => $enrolledCourse->id,
            'scope' => 'level',
            'target_track' => 'Advanced',
        ]);

        Assignment::query()->create([
            'name' => 'Visible Personal Assignment',
            'course_id' => $enrolledCourse->id,
            'scope' => 'personal',
            'target_user_id' => $student->id,
        ]);

        Assignment::query()->create([
            'name' => 'Hidden Other Personal Assignment',
            'course_id' => $hiddenCourse->id,
            'scope' => 'personal',
            'target_user_id' => $otherStudent->id,
        ]);

        $response = $this->actingAs($student)->followingRedirects()->get(route('student.assignments'));

        $response->assertOk();
        $response->assertSee('Visible General Assignment');
        $response->assertSee('Visible Beginner Assignment');
        $response->assertSee('Visible Personal Assignment');
        $response->assertDontSee('Hidden Advanced Assignment');
        $response->assertDontSee('Hidden Other Personal Assignment');
    }

    public function test_student_materials_page_shows_only_visible_materials(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Intermediate',
        ]);

        $otherStudent = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $enrolledCourse = Course::query()->create([
            'title' => 'Intermediate Science',
            'code' => 'ISC-102',
            'is_active' => true,
        ]);

        $hiddenCourse = Course::query()->create([
            'title' => 'Hidden Science',
            'code' => 'HSC-103',
            'is_active' => true,
        ]);

        $student->courses()->attach($enrolledCourse->id);

        LearningMaterial::query()->create([
            'title' => 'Visible General Material',
            'course_id' => $enrolledCourse->id,
            'material_type' => 'File',
            'scope' => 'all',
        ]);

        LearningMaterial::query()->create([
            'title' => 'Visible Intermediate Material',
            'course_id' => $enrolledCourse->id,
            'material_type' => 'File',
            'scope' => 'level',
            'target_track' => 'Intermediate',
        ]);

        LearningMaterial::query()->create([
            'title' => 'Hidden Beginner Material',
            'course_id' => $enrolledCourse->id,
            'material_type' => 'File',
            'scope' => 'level',
            'target_track' => 'Beginner',
        ]);

        LearningMaterial::query()->create([
            'title' => 'Visible Personal Material',
            'course_id' => $enrolledCourse->id,
            'material_type' => 'File',
            'scope' => 'personal',
            'target_user_id' => $student->id,
        ]);

        LearningMaterial::query()->create([
            'title' => 'Hidden Other Personal Material',
            'course_id' => $hiddenCourse->id,
            'material_type' => 'File',
            'scope' => 'personal',
            'target_user_id' => $otherStudent->id,
        ]);

        $response = $this->actingAs($student)->followingRedirects()->get(route('student.materials'));

        $response->assertOk();
        $response->assertSee('Visible General Material');
        $response->assertSee('Visible Intermediate Material');
        $response->assertSee('Visible Personal Material');
        $response->assertDontSee('Hidden Beginner Material');
        $response->assertDontSee('Hidden Other Personal Material');
    }
}
