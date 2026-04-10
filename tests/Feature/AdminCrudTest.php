<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_admin_routes(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $this->actingAs($student)
            ->get(route('admin.overview'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/manage')
            ->assertForbidden();
    }

    public function test_admin_can_access_filament_admin_panel_pages(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get('/manage')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/manage/courses')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/manage/assignments')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/manage/assessments')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/manage/learning-materials')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/manage/users')
            ->assertOk();
    }

    public function test_legacy_admin_named_routes_redirect_to_filament(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.overview'))
            ->assertRedirect('/manage');

        $this->actingAs($admin)
            ->get(route('admin.students'))
            ->assertRedirect('/manage/users');

        $this->actingAs($admin)
            ->get(route('admin.courses'))
            ->assertRedirect('/manage/courses');

        $this->actingAs($admin)
            ->get(route('admin.assignments'))
            ->assertRedirect('/manage/assignments');

        $this->actingAs($admin)
            ->get(route('admin.assessments'))
            ->assertRedirect('/manage/assessments');

        $this->actingAs($admin)
            ->get(route('admin.materials'))
            ->assertRedirect('/manage/learning-materials');
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'track' => 'Advanced',
        ]);
    }
}
