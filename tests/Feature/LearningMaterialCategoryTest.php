<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LearningMaterialCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_material_category_constants_and_options(): void
    {
        $options = LearningMaterial::categoryOptions();

        $this->assertArrayHasKey('Curriculum', $options);
        $this->assertArrayHasKey('Study Material', $options);
        $this->assertArrayHasKey('Quiz Preps', $options);
        $this->assertArrayHasKey('Answer Kits', $options);
        $this->assertArrayHasKey('Project Guides', $options);
        $this->assertArrayHasKey('Cheat Sheets', $options);
        $this->assertArrayHasKey('Practice Exercises', $options);
        $this->assertArrayHasKey('Past Papers', $options);
        $this->assertArrayHasKey('Rules', $options);
        $this->assertArrayHasKey('General Notices', $options);
        $this->assertArrayHasKey('Supplementary Resources', $options);
    }

    public function test_instructor_can_create_learning_material_with_new_categories(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create(['title' => 'Web Development', 'code' => 'WD101', 'is_active' => true]);
        $course->instructors()->attach($instructor->id);

        $material1 = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Midterm Quiz Prep Guide',
            'category' => 'Quiz Preps',
            'material_type' => 'Document',
            'scope' => 'all',
        ]);

        $material2 = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Lab 4 Answer Kit & Solution Walkthrough',
            'category' => 'Answer Kits',
            'material_type' => 'Document',
            'scope' => 'all',
        ]);

        $this->assertDatabaseHas('learning_materials', [
            'id' => $material1->id,
            'category' => 'Quiz Preps',
        ]);

        $this->assertDatabaseHas('learning_materials', [
            'id' => $material2->id,
            'category' => 'Answer Kits',
        ]);
    }

    public function test_student_can_filter_learning_materials_by_new_category(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create(['title' => 'Web Development', 'code' => 'WD101', 'is_active' => true]);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'CSS Flexbox Cheat Sheet',
            'category' => 'Cheat Sheets',
            'material_type' => 'Document',
            'scope' => 'all',
        ]);

        LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Quiz 1 Study Guide',
            'category' => 'Quiz Preps',
            'material_type' => 'Document',
            'scope' => 'all',
        ]);

        $this->actingAs($student);

        Livewire::test(\App\Filament\Student\Pages\Materials::class)
            ->set('filterCategory', 'Quiz Preps')
            ->assertSee('Quiz 1 Study Guide')
            ->assertDontSee('CSS Flexbox Cheat Sheet');
    }
}
