<?php

namespace Tests\Feature;

use App\Filament\Widgets\AdminStatsWidget;
use App\Filament\Widgets\RecentActivitiesWidget;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardRefactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_dashboard_renders_hero_banner_and_stat_cards(): void
    {
        $admin = User::factory()->create([
            'name' => 'Dr. Admin User',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Advanced Cyber Security',
            'code' => 'CYB-401',
            'is_active' => true,
        ]);

        CourseIntake::query()->create([
            'course_id' => $course->id,
            'name' => 'Cohort 2026-A',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        Assessment::query()->create([
            'name' => 'Midterm Security Exam',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(5),
        ]);

        Assignment::query()->create([
            'name' => 'Network Packet Analysis',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(7),
        ]);

        LearningMaterial::query()->create([
            'title' => 'Wireshark Cheat Sheet',
            'course_id' => $course->id,
        ]);

        $this->actingAs($admin);

        $response = $this->followingRedirects()->get('/manage');
        $response->assertSuccessful();

        Livewire::test(AdminStatsWidget::class)
            ->assertSuccessful()
            ->assertSee('Welcome back, Dr. Admin User!')
            ->assertSee('View Classrooms')
            ->assertSee('Active Learners')
            ->assertSee('Thinker HUB • Admin Portal')
            ->assertSee('Registered Students')
            ->assertSee('Assigned Assessments')
            ->assertSee('Published Assignments')
            ->assertSee('Learning Materials');

        Livewire::test(RecentActivitiesWidget::class)
            ->assertSuccessful()
            ->assertSee('Recent Platform Activities')
            ->assertSee('Audit Stream');
    }

    public function test_admin_stats_widget_livewire_component_mounts(): void
    {
        $admin = User::factory()->create([
            'name' => 'Super Administrator',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminStatsWidget::class)
            ->assertSuccessful()
            ->assertSee('Welcome back, Super Administrator!')
            ->assertSee('Registered Students')
            ->assertSee('Assigned Assessments')
            ->assertSee('Published Assignments')
            ->assertSee('Learning Materials');
    }

    public function test_recent_activities_widget_renders_collapsible_container(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Officer',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Alice Wonder',
            'role' => 'student',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(RecentActivitiesWidget::class)
            ->assertSuccessful()
            ->assertSee('Recent Platform Activities')
            ->assertSee('Audit Stream')
            ->assertSee('Alice Wonder');
    }
}
