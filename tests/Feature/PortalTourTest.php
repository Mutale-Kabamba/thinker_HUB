<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PortalTourService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_tour_service_provides_steps_for_all_portals(): void
    {
        $service = app(PortalTourService::class);
        $portals = ['student', 'instructor', 'admin', 'parent', 'contributor'];

        foreach ($portals as $portal) {
            $steps = $service->getStepsForPortal($portal);
            $this->assertIsArray($steps);
            $this->assertNotEmpty($steps, "Portal {$portal} should have tour steps defined.");

            foreach ($steps as $step) {
                $this->assertArrayHasKey('popover', $step);
                $this->assertArrayHasKey('title', $step['popover']);
                $this->assertArrayHasKey('description', $step['popover']);
                $this->assertNotEmpty($step['popover']['title']);
                $this->assertNotEmpty($step['popover']['description']);
            }
        }

        // Specifically verify that Student tour captures all sections, resources, and evaluation workflows
        $studentSteps = $service->getStepsForPortal('student');
        $studentTitles = array_map(fn ($step) => $step['popover']['title'], $studentSteps);

        $expectedStudentKeywords = [
            'Student Portal',
            'Announcements',
            'Ranking',
            'Learning Status',
            'Enrolled Courses',
            'Calendar',
            'Agenda',
            'Courses',
            'Materials',
            'Resource Library',
            'Study Buddy',
            'Certificates',
            'Assignments',
            'Assessments',
            'Quizzes',
            'Schedule',
            'Community',
            'Opportunities',
            'Claim Hub',
            'Reviews',
            'Settings',
            'Search',
            'Replay Tour',
        ];

        foreach ($expectedStudentKeywords as $keyword) {
            $found = false;
            foreach ($studentTitles as $title) {
                if (stripos($title, $keyword) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Student tour is missing expected section/step for '{$keyword}'");
        }

        // Specifically verify Instructor tour captures all teaching workflows and tools
        $instructorSteps = $service->getStepsForPortal('instructor');
        $instructorTitles = array_map(fn ($step) => $step['popover']['title'], $instructorSteps);

        $expectedInstructorKeywords = [
            'Instructor Operations Hub',
            'AI Teaching Co-Pilot',
            'Assigned Courses',
            'Intakes & Cohorts',
            'Class Sessions',
            'Attendance Registers',
            'Schedule',
            'Learning Materials',
            'Video Guides',
            'Assignments',
            'Assignment Submissions',
            'Course Assessments',
            'Assessment Grading',
            'Quizzes',
            'Student Results',
            'Student Roster',
            'Cohort Broadcasts',
            'Gamification',
            'Reports',
            'Analytics',
            'Articles & Tutorials',
            'Settings',
            'Quick AI',
            'Replay Tour',
        ];

        foreach ($expectedInstructorKeywords as $keyword) {
            $found = false;
            foreach ($instructorTitles as $title) {
                if (stripos($title, $keyword) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Instructor tour is missing expected section/step for '{$keyword}'");
        }

        // Specifically verify Admin tour captures all platform management tools
        $adminSteps = $service->getStepsForPortal('admin');
        $adminTitles = array_map(fn ($step) => $step['popover']['title'], $adminSteps);

        $expectedAdminKeywords = [
            'Administration Hub',
            'Executive Stats',
            'User Accounts',
            'Student Registry',
            'Instructor Directory',
            'Instructor Recruitment',
            'Course Catalog',
            'Intakes',
            'Course Sessions',
            'Learning Materials',
            'Video Guide Repositories',
            'Assignments',
            'Assessments',
            'Quizzes',
            'Attendance Oversight',
            'Accounting Dashboard',
            'Promotions',
            'Reports',
            'Career Opportunities',
            'Gamification Economy',
            'Community Moderation',
            'Platform Settings',
            'Global Search',
            'Replay Tour',
        ];

        foreach ($expectedAdminKeywords as $keyword) {
            $found = false;
            foreach ($adminTitles as $title) {
                if (stripos($title, $keyword) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Admin tour is missing expected section/step for '{$keyword}'");
        }

        // Specifically verify Parent tour captures student tracking tools
        $parentSteps = $service->getStepsForPortal('parent');
        $parentTitles = array_map(fn ($step) => $step['popover']['title'], $parentSteps);

        $expectedParentKeywords = [
            'Parent & Guardian Portal',
            'Parent Overview',
            'Connected Student Profiles',
            'Attendance Records',
            'Student Achievements',
            'Course Syllabuses',
            'Direct Instructor Chat',
            'Parent Notifications',
            'Replay Tour',
        ];

        foreach ($expectedParentKeywords as $keyword) {
            $found = false;
            foreach ($parentTitles as $title) {
                if (stripos($title, $keyword) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Parent tour is missing expected section/step for '{$keyword}'");
        }

        // Specifically verify Contributor tour captures publishing tools
        $contributorSteps = $service->getStepsForPortal('contributor');
        $contributorTitles = array_map(fn ($step) => $step['popover']['title'], $contributorSteps);

        $expectedContributorKeywords = [
            'Contributor Studio',
            'Contributor Overview',
            'Articles & Tutorials Studio',
            'Publishing Workflow',
            'Author Bio',
            'Article Search',
            'Replay Tour',
        ];

        foreach ($expectedContributorKeywords as $keyword) {
            $found = false;
            foreach ($contributorTitles as $title) {
                if (stripos($title, $keyword) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Contributor tour is missing expected section/step for '{$keyword}'");
        }
    }

    public function test_user_model_tracks_tour_completions_independently(): void
    {
        $user = User::factory()->create([
            'completed_tours' => null,
        ]);

        $this->assertFalse($user->hasCompletedTour('student'));
        $this->assertFalse($user->hasCompletedTour('instructor'));

        $user->completeTour('student');
        $user->refresh();

        $this->assertTrue($user->hasCompletedTour('student'));
        $this->assertFalse($user->hasCompletedTour('instructor'));

        $user->completeTour('instructor');
        $user->refresh();

        $this->assertTrue($user->hasCompletedTour('student'));
        $this->assertTrue($user->hasCompletedTour('instructor'));
        $this->assertFalse($user->hasCompletedTour('admin'));

        $user->resetTour('student');
        $user->refresh();

        $this->assertFalse($user->hasCompletedTour('student'));
        $this->assertTrue($user->hasCompletedTour('instructor'));
    }

    public function test_unauthenticated_user_cannot_access_tour_endpoints(): void
    {
        $completeResponse = $this->postJson(route('portal-tour.complete'), ['portal' => 'student']);
        $completeResponse->assertUnauthorized();

        $resetResponse = $this->postJson(route('portal-tour.reset'), ['portal' => 'student']);
        $resetResponse->assertUnauthorized();
    }

    public function test_authenticated_user_can_complete_portal_tour_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('portal-tour.complete'), [
            'portal' => 'student',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'portal' => 'student',
                'completed' => true,
            ]);

        $user->refresh();
        $this->assertTrue($user->hasCompletedTour('student'));
    }

    public function test_authenticated_user_can_reset_portal_tour_via_api(): void
    {
        $user = User::factory()->create([
            'completed_tours' => ['student', 'admin'],
        ]);

        $response = $this->actingAs($user)->postJson(route('portal-tour.reset'), [
            'portal' => 'student',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'portal' => 'student',
                'completed' => false,
            ]);

        $user->refresh();
        $this->assertFalse($user->hasCompletedTour('student'));
        $this->assertTrue($user->hasCompletedTour('admin'));
    }

    public function test_invalid_portal_name_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('portal-tour.complete'), [
            'portal' => 'invalid-portal',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['portal']);
    }

    public function test_portal_tour_blade_views_render_properly(): void
    {
        $user = User::factory()->create();

        $buttonHtml = view('filament.partials.portal-tour-button')->render();
        $this->assertStringContainsString('portal-tour-trigger-btn', $buttonHtml);
        $this->assertStringContainsString('startPortalTour', $buttonHtml);

        $tourHtml = view('filament.partials.portal-tour', ['portal' => 'student'])->render();
        $this->assertStringContainsString('vendor/driverjs/driver.js', $tourHtml);
        $this->assertStringContainsString('vendor/driverjs/driver.css', $tourHtml);
        $this->assertStringContainsString('window.startPortalTour', $tourHtml);
        $this->assertStringContainsString('Welcome to Your Student Portal', $tourHtml);
    }
}

