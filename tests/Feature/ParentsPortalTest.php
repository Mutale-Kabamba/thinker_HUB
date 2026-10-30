<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Community;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParentsPortalTest extends TestCase
{
    use RefreshDatabase;

    private function createCourse(array $attributes = []): Course
    {
        static $sequence = 1;
        $seq = $sequence++;

        return Course::create(array_merge([
            'title' => 'Test Course ' . $seq,
            'code' => 'TC' . $seq,
            'overview' => 'Test course overview',
            'is_active' => true,
            'is_for_kids' => false,
        ], $attributes));
    }

    public function test_course_can_be_marked_as_for_kids(): void
    {
        $kidsCourse = $this->createCourse([
            'title' => 'Scratch Programming for Kids',
            'is_for_kids' => true,
        ]);

        $generalCourse = $this->createCourse([
            'title' => 'Fullstack Laravel & Vue',
            'is_for_kids' => false,
        ]);

        $this->assertTrue($kidsCourse->isForKids());
        $this->assertFalse($generalCourse->isForKids());

        $this->assertCount(1, Course::forKids()->get());
        $this->assertCount(1, Course::general()->get());
    }

    public function test_parent_kid_relationship_and_helpers(): void
    {
        $parent = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.parent@example.com',
            'role' => 'student',
        ]);

        $kid = User::factory()->create([
            'name' => 'Timmy Doe',
            'email' => 'timmy@example.com',
            'role' => 'student',
            'is_kid' => true,
        ]);

        $parent->kids()->attach($kid->id, [
            'relationship' => 'Father',
            'notes' => 'Grade 4 student',
        ]);

        $this->assertTrue($parent->isParent());
        $this->assertTrue($kid->isKid());
        $this->assertTrue($parent->canSwitchToParent());

        $this->assertCount(1, $parent->kids);
        $this->assertEquals('Timmy Doe', $parent->kids->first()->name);

        $this->assertCount(1, $kid->parents);
        $this->assertEquals('John Doe', $kid->parents->first()->name);
    }

    public function test_privacy_rule_1_kids_excluded_from_public_community(): void
    {
        // General student
        $regularStudent = User::factory()->create([
            'name' => 'Alice Adult',
            'role' => 'student',
            'is_kid' => false,
            'is_active' => true,
        ]);

        // Kid student with is_kid = true
        $kidStudent = User::factory()->create([
            'name' => 'Bobby Kid',
            'role' => 'student',
            'is_kid' => true,
            'is_active' => true,
        ]);

        // Kid enrolled in a kids course
        $kidsCourse = $this->createCourse(['is_for_kids' => true]);
        $kidInCourse = User::factory()->create([
            'name' => 'Charlie Child',
            'role' => 'student',
            'is_kid' => false,
            'is_active' => true,
        ]);
        Enrollment::create([
            'user_id' => $kidInCourse->id,
            'course_id' => $kidsCourse->id,
        ]);

        // Acting as regular student viewing Community
        $this->actingAs($regularStudent);

        $excludedIds = Community::getExcludedKidUserIds();
        $this->assertContains($kidStudent->id, $excludedIds);
        $this->assertContains($kidInCourse->id, $excludedIds);
        $this->assertNotContains($regularStudent->id, $excludedIds);

        // Test Livewire Community component excludes kids from directory
        $component = Livewire::test(Community::class);
        $directoryUsers = collect($component->get('directory'))->pluck('id')->all();

        $this->assertNotContains($kidStudent->id, $directoryUsers);
        $this->assertNotContains($kidInCourse->id, $directoryUsers);
    }

    public function test_privacy_rule_2_parents_can_only_view_parents_in_same_course(): void
    {
        // Course A (Kids Robotics)
        $courseA = $this->createCourse(['title' => 'Kids Robotics', 'is_for_kids' => true]);

        // Course B (Kids Art)
        $courseB = $this->createCourse(['title' => 'Kids Art', 'is_for_kids' => true]);

        // Parent 1 with Kid 1 in Course A
        $parent1 = User::factory()->create(['name' => 'Parent 1']);
        $kid1 = User::factory()->create(['name' => 'Kid 1', 'is_kid' => true]);
        $parent1->kids()->attach($kid1->id, ['relationship' => 'Mother']);
        Enrollment::create(['user_id' => $kid1->id, 'course_id' => $courseA->id]);

        // Parent 2 with Kid 2 in Course A (Peer parent!)
        $parent2 = User::factory()->create(['name' => 'Parent 2 (Peer)']);
        $kid2 = User::factory()->create(['name' => 'Kid 2', 'is_kid' => true]);
        $parent2->kids()->attach($kid2->id, ['relationship' => 'Father']);
        Enrollment::create(['user_id' => $kid2->id, 'course_id' => $courseA->id]);

        // Parent 3 with Kid 3 in Course B ONLY (Unrelated parent!)
        $parent3 = User::factory()->create(['name' => 'Parent 3 (Unrelated)']);
        $kid3 = User::factory()->create(['name' => 'Kid 3', 'is_kid' => true]);
        $parent3->kids()->attach($kid3->id, ['relationship' => 'Father']);
        Enrollment::create(['user_id' => $kid3->id, 'course_id' => $courseB->id]);

        // When Parent 1 queries peer parents:
        $peers = $parent1->getCoursePeerParents();
        $peerIds = $peers->pluck('id')->all();

        // Must include Parent 2 (same course)
        $this->assertContains($parent2->id, $peerIds);

        // Must NOT include Parent 1 themselves
        $this->assertNotContains($parent1->id, $peerIds);

        // Must NOT include Parent 3 (enrolled only in different course)
        $this->assertNotContains($parent3->id, $peerIds);

        // When filtered specifically by Course A:
        $peersCourseA = $parent1->getCoursePeerParents($courseA->id);
        $this->assertContains($parent2->id, $peersCourseA->pluck('id')->all());
        $this->assertNotContains($parent3->id, $peersCourseA->pluck('id')->all());
    }

    public function test_parent_portal_routes_accessible_to_parent(): void
    {
        $parent = User::factory()->create([
            'name' => 'Jane Parent',
            'email' => 'jane.parent@example.com',
            'role' => 'student',
        ]);

        $kid = User::factory()->create([
            'name' => 'Sammy Child',
            'is_kid' => true,
        ]);

        $parent->kids()->attach($kid->id, ['relationship' => 'Mother']);

        $response = $this->actingAs($parent)->get('/parent/overview');
        $response->assertSuccessful();

        $responseKids = $this->actingAs($parent)->get('/parent/manage-kids');
        $responseKids->assertSuccessful();

        $responseActivities = $this->actingAs($parent)->get('/parent/kids-activities');
        $responseActivities->assertSuccessful();

        $responseLeaderboard = $this->actingAs($parent)->get('/parent/kids-leaderboard');
        $responseLeaderboard->assertSuccessful();

        $responseCircle = $this->actingAs($parent)->get('/parent/course-parents');
        $responseCircle->assertSuccessful();

        $responseSettings = $this->actingAs($parent)->get('/parent/parent-settings');
        $responseSettings->assertSuccessful();
    }
}
