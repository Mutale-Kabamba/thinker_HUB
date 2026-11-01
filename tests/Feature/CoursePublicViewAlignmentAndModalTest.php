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

    public function test_compound_skill_levels_combine_descriptions_appropriately(): void
    {
        $course = Course::create([
            'title' => 'Python Mastery: From Zero to a Deployed REST API',
            'slug' => 'python-mastery-from-zero-to-a-deployed-rest-api',
            'code' => 'PY-REST-101',
            'description' => 'Complete Python curriculum.',
            'overview' => 'Detailed Python overview text.',
            'key_outcome' => 'Deploy production ready FastAPI REST APIs.',
            'is_active' => true,
            'is_open_enrollment' => true,
            'fees' => json_encode([
                'group' => [
                    [
                        'level' => 'Advanced (Beginner to Advanced)',
                        'amount' => 'K2000.00',
                        'duration' => '12 Weeks',
                    ],
                    [
                        'level' => 'Advanced (Intermediate to Advanced)',
                        'amount' => 'K1400.00',
                        'duration' => '8 Weeks',
                    ],
                    [
                        'level' => 'Beginner',
                        'amount' => 'K600.00',
                        'duration' => '4 Weeks',
                    ],
                ],
            ]),
            'level_progression' => json_encode([
                [
                    'level' => 'Beginner',
                    'details' => 'Learn syntax, data structures, variables, loops, and functions.',
                ],
                [
                    'level' => 'Intermediate',
                    'details' => 'OOP, modules, packaging, and building robust CLI tools.',
                ],
                [
                    'level' => 'Advanced',
                    'details' => 'FastAPI, SQLAlchemy, JWT auth, and Docker container deployment.',
                ],
            ]),
        ]);

        $url = route('landing.courses.show', [
            'course' => $course->id,
            'slug' => Str::slug($course->title),
        ]);

        $response = $this->get($url);
        $response->assertOk();

        $content = $response->getContent();

        // 1. Verify Advanced (Beginner to Advanced) combines all 3 level descriptions
        $this->assertStringContainsString('Advanced (Beginner to Advanced)', $content);
        // The modal data for Advanced (Beginner to Advanced) must contain Beginner, Intermediate, and Advanced milestones
        $this->assertMatchesRegularExpression(
            '/window\.openSkillLevelModal\(.*Advanced \(Beginner to Advanced\).*Learn syntax.*OOP, modules.*FastAPI, SQLAlchemy/s',
            $content
        );

        // 2. Verify Advanced (Intermediate to Advanced) only gets Intermediate and Advanced descriptions
        $this->assertStringContainsString('Advanced (Intermediate to Advanced)', $content);

        // Find the window.openSkillLevelModal call specifically for Advanced (Intermediate to Advanced)
        $pos = strpos($content, 'window.openSkillLevelModal');
        while ($pos !== false) {
            $chunk = substr($content, $pos, 1500);
            if (str_contains($chunk, 'Advanced (Intermediate to Advanced)')) {
                $snippet = $chunk;
                break;
            }
            $pos = strpos($content, 'window.openSkillLevelModal', $pos + 1);
        }

        $this->assertNotNull($snippet ?? null, 'Skill level modal for Advanced (Intermediate to Advanced) not found');
        $this->assertStringNotContainsString('Beginner Milestone', $snippet);
        $this->assertStringContainsString('Intermediate Milestone', $snippet);
        $this->assertStringContainsString('Advanced Milestone', $snippet);
    }

    public function test_fee_options_preserve_compound_levels_and_checkout_accepts_them(): void
    {
        $course = Course::create([
            'title' => 'Fullstack Mastery Test',
            'code' => 'TEST-301',
            'overview' => 'Fullstack description',
            'description' => 'Fullstack description',
            'key_outcome' => 'Deploy fullstack app',
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
        ]);

        $feeOptions = $course->getFeeOptions();
        $this->assertCount(2, $feeOptions);
        $this->assertSame('Advanced (Beginner to Advanced)', $feeOptions[0]['level']);
        $this->assertSame(1500.0, $feeOptions[0]['amount']);
        $this->assertSame('Beginner', $feeOptions[1]['level']);
        $this->assertSame(500.0, $feeOptions[1]['amount']);

        // Test checkout page with compound track
        $checkoutUrl = route('checkout.show', [
            'course' => $course->id,
            'track' => 'Advanced (Beginner to Advanced)',
            'mode' => 'group',
        ]);

        $response = $this->get($checkoutUrl);
        $response->assertOk();
        $response->assertSee('Advanced (Beginner to Advanced)');
        $response->assertSee('1,500.00');
    }
}

