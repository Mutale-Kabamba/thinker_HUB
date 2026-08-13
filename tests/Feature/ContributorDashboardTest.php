<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_blogger_redirected_to_contributor_dashboard(): void
    {
        $blogger = User::factory()->create([
            'role' => 'blogger',
            'is_active' => true,
        ]);

        $response = $this->actingAs($blogger)->get('/dashboard');

        $response->assertRedirect('/contribute');
    }

    public function test_researcher_redirected_to_contributor_dashboard(): void
    {
        $researcher = User::factory()->create([
            'role' => 'researcher',
            'is_active' => true,
        ]);

        $response = $this->actingAs($researcher)->get('/dashboard');

        $response->assertRedirect('/contribute');
    }

    public function test_employer_redirected_to_contributor_dashboard(): void
    {
        $employer = User::factory()->create([
            'role' => 'employer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($employer)->get('/dashboard');

        $response->assertRedirect('/contribute');
    }

    public function test_contributor_panel_access_isolation(): void
    {
        $blogger = User::factory()->create(['role' => 'blogger', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $this->assertTrue($blogger->isContributor());
        $this->assertFalse($student->isContributor());

        $contributorPanel = filament()->getPanel('contributor');
        $studentPanel = filament()->getPanel('student');

        $this->assertTrue($blogger->canAccessPanel($contributorPanel));
        $this->assertFalse($blogger->canAccessPanel($studentPanel));

        $this->assertFalse($student->canAccessPanel($contributorPanel));
        $this->assertTrue($student->canAccessPanel($studentPanel));
    }
}
