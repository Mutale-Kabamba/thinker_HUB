<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CoursePublicViewAlignmentAndModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_course_view_renders_aligned_fees_justified_text_and_progression_modal(): void
    {
        $course = Course::create([
            'title' => 'Full-Stack Robotics & Web Development',
            'slug' => 'full-stack-robotics-web-development',
            'code' => 'ROB-WEB-500',
            'description' => 'A comprehensive bootcamp for aspiring developers and roboticists.',
            'overview' => 'Detailed overview text that covers all aspects of curriculum, theory, and hands-on lab sessions.',
            'key_outcome' => 'Learners will build and deploy 3 production applications and 1 autonomous robotics control interface.',
            'is_active' => true,
            'is_open_enrollment' => true,
            'fees' => json_encode([
                'group' => [
                    [
                        'level' => 'Advanced (Beginner to Advanced)',
                        'amount' => 'K1500.00',
                        'duration' => '10 Weeks',
                    ],
                    [
                        'level' => 'Beginner',
                        'amount' => 'K500.00',
                        'duration' => '4 Weeks',
                    ],
                ],
            ]),
            'level_progression' => json_encode([
                [
                    'level' => 'Beginner',
                    'details' => 'Foundations of robotics, sensors, and basic syntax.',
                ],
                [
                    'level' => 'Advanced (Beginner to Advanced)',
                    'details' => 'Full end-to-end journey from electronics assembly to cloud deployment.',
                ],
            ]),
        ]);

        $url = route('landing.courses.show', [
            'course' => $course->id,
            'slug' => Str::slug($course->title),
        ]);

        $response = $this->get($url);

        $response->assertOk();

        // 1. Course Overview & Key Outcome must have text-justify
        $response->assertSee('text-justify');
        $response->assertSee($course->overview);
        $response->assertSee($course->key_outcome);

        // 2. Fees section should have left-aligned level, right-aligned amount, right-aligned duration
        $response->assertSee('hub-fee-row grid grid-cols-[1fr_100px_80px]', false);
        $response->assertSee('Advanced (Beginner to Advanced)');
        $response->assertSee('K1500.00');
        $response->assertSee('10 Weeks');
        $response->assertSee('Beginner');
        $response->assertSee('K500.00');
        $response->assertSee('4 Weeks');

        // 3. Hover indicator (Click to View) must be present
        $response->assertSee('(Click to View)');

        // 4. Modal trigger must be present
        $response->assertSee('window.openSkillLevelModal', false);

        // 5. Skill Level Progression Modal must be included on the page
        $response->assertSee('id="skill-level-progression-modal"', false);
        $response->assertSee('Skill Level Progression Roadmap Framework &amp; Guidelines', false);
    }
}
