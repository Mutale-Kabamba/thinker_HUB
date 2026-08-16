<?php

namespace Tests\Feature;

use App\Livewire\MaterialReader;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PDFMaterialReaderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_reader_mounts_with_material_details(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'title' => 'Software Engineering',
            'code' => 'CS201',
            'is_active' => true,
        ]);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Lecture 1: Architecture Notes',
            'category' => 'Study Material',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'architecture.pdf',
            'file_path' => 'materials/architecture.pdf',
        ]);

        $student->courses()->attach($course->id);

        $this->actingAs($student);

        Livewire::test(MaterialReader::class, ['material' => $material])
            ->assertSet('material.id', $material->id)
            ->assertSet('pointsEarned', false)
            ->assertSee('Lecture 1: Architecture Notes')
            ->assertSee('Read 3 min for +5 XP / +2 TC');
    }

    public function test_student_earns_points_when_actively_reading_for_at_least_170_seconds(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $course = Course::create([
            'title' => 'Database Systems',
            'code' => 'CS301',
            'is_active' => true,
        ]);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Relational Algebra & Normalization',
            'category' => 'Cheat Sheets',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'normalization.pdf',
            'file_path' => 'materials/normalization.pdf',
        ]);

        $student->courses()->attach($course->id);

        $this->actingAs($student);

        Livewire::test(MaterialReader::class, ['material' => $material])
            ->call('awardReadingPoints', [
                'activeSeconds' => 180,
            ])
            ->assertSet('pointsEarned', true)
            ->assertDispatched('points-awarded');

        $student->refresh();
        $this->assertEquals(5, $student->lifetime_xp);
        $this->assertEquals(2, $student->spendable_coins);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'activity_type' => 'material_read',
            'subject_type' => LearningMaterial::class,
            'subject_id' => $material->id,
            'amount_xp' => 5,
            'amount_coins' => 2,
        ]);
    }

    public function test_server_rejects_point_claim_when_reading_time_is_below_threshold(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $course = Course::create([
            'title' => 'Operating Systems',
            'code' => 'CS401',
            'is_active' => true,
        ]);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Concurrency & Synchronization',
            'category' => 'Study Material',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'concurrency.pdf',
            'file_path' => 'materials/concurrency.pdf',
        ]);

        $student->courses()->attach($course->id);

        $this->actingAs($student);

        // Active seconds is only 45s (far below 170s requirement)
        Livewire::test(MaterialReader::class, ['material' => $material])
            ->call('awardReadingPoints', [
                'activeSeconds' => 45,
            ])
            ->assertSet('pointsEarned', false)
            ->assertNotDispatched('points-awarded');

        $student->refresh();
        $this->assertEquals(0, $student->lifetime_xp);
        $this->assertEquals(0, $student->spendable_coins);
        $this->assertDatabaseCount('xp_transactions', 0);
    }

    public function test_duplicate_reading_claims_for_same_material_are_prevented(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        $course = Course::create([
            'title' => 'Web Development',
            'code' => 'CS101',
            'is_active' => true,
        ]);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'HTML5 & CSS3 Master Guide',
            'category' => 'Study Material',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'guide.pdf',
            'file_path' => 'materials/guide.pdf',
        ]);

        $student->courses()->attach($course->id);

        $this->actingAs($student);

        $component = Livewire::test(MaterialReader::class, ['material' => $material]);

        // First Claim
        $component->call('awardReadingPoints', [
            'activeSeconds' => 180,
        ])->assertSet('pointsEarned', true);

        $student->refresh();
        $this->assertEquals(5, $student->lifetime_xp);
        $this->assertEquals(2, $student->spendable_coins);

        // Duplicate Claim Attempt
        $component->call('awardReadingPoints', [
            'activeSeconds' => 200,
        ]);

        // Should still be only 5 XP and 2 Coins
        $student->refresh();
        $this->assertEquals(5, $student->lifetime_xp);
        $this->assertEquals(2, $student->spendable_coins);
        $this->assertDatabaseCount('xp_transactions', 1);
    }

    public function test_material_reader_recognizes_previously_completed_reading_on_mount(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'title' => 'Cloud Computing',
            'code' => 'CS501',
            'is_active' => true,
        ]);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Kubernetes Handbook',
            'category' => 'Project Guides',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'k8s.pdf',
            'file_path' => 'materials/k8s.pdf',
        ]);

        $student->courses()->attach($course->id);

        // Pre-create XP transaction
        XpTransaction::create([
            'user_id' => $student->id,
            'amount_xp' => 5,
            'amount_coins' => 2,
            'activity_type' => 'material_read',
            'subject_type' => LearningMaterial::class,
            'subject_id' => $material->id,
            'points' => 5,
            'source' => 'material_read',
            'source_id' => (string) $material->id,
            'description' => 'Read learning material',
        ]);

        $this->actingAs($student);

        Livewire::test(MaterialReader::class, ['material' => $material])
            ->assertSet('pointsEarned', true)
            ->assertSee('Points Awarded (+5 XP / +2 TC)');
    }

    public function test_material_reader_route_is_accessible(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'title' => 'Machine Learning',
            'code' => 'CS601',
            'is_active' => true,
        ]);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Linear Regression & Calculus',
            'category' => 'Study Material',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'ml.pdf',
            'file_path' => 'materials/ml.pdf',
        ]);

        $student->courses()->attach($course->id);

        $response = $this->actingAs($student)->get(route('materials.read', $material));

        $response->assertOk();
        $response->assertSee('Linear Regression &amp; Calculus', false);
        $response->assertSee('pdfMaterialTracker', false);
    }
}
