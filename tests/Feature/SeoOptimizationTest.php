<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeoOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_and_course_page_render_valid_json_ld_and_seo_meta(): void
    {
        $course = Course::create([
            'title' => 'Data Analytics & Python Mastery',
            'code' => 'DA-202',
            'overview' => 'Master practical data analytics with Python, Pandas, and SQL in hands-on sessions.',
            'fees' => '1200',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        // 1. Test Homepage SEO & JSON-LD
        $homeResponse = $this->get('/');
        $homeResponse->assertOk();
        $homeHtml = $homeResponse->getContent();

        $this->assertStringNotContainsString('$__contextArgs', $homeHtml);
        $this->assertStringContainsString('"@context":"https://schema.org"', $homeHtml);
        $this->assertStringContainsString('"@type":"EducationalOrganization"', $homeHtml);
        $this->assertStringContainsString('"@type":"WebSite"', $homeHtml);
        $this->assertStringContainsString('"@type":"SearchAction"', $homeHtml);
        $this->assertStringContainsString('<meta name="geo.region" content="ZM">', $homeHtml);

        // 2. Test Course Page SEO & Course Schema
        $slug = Str::slug($course->title);
        $courseResponse = $this->get("/courses/{$course->id}/{$slug}");
        $courseResponse->assertOk();
        $courseHtml = $courseResponse->getContent();

        $this->assertStringNotContainsString('$__contextArgs', $courseHtml);
        $this->assertStringContainsString('"@context":"https://schema.org"', $courseHtml);
        $this->assertStringContainsString('"@type":"Course"', $courseHtml);
        $this->assertStringContainsString('"courseCode":"DA-202"', $courseHtml);
        $this->assertStringContainsString('"priceCurrency":"ZMW"', $courseHtml);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $courseHtml);

        // Extract and decode JSON-LD to verify strict JSON validity
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $courseHtml, $matches);
        $this->assertNotEmpty($matches[1] ?? '');
        $decoded = json_decode(trim($matches[1]), true);
        $this->assertIsArray($decoded);
        $this->assertEquals('https://schema.org', $decoded['@context'] ?? null);
        $this->assertNotEmpty($decoded['@graph'] ?? []);
    }

    public function test_sitemap_command_and_routes_generate_valid_xml_including_courses(): void
    {
        Course::create([
            'title' => 'Cloud Engineering Bootcamp',
            'code' => 'CLD-101',
            'fees' => '1500',
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Grace Banda',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->artisan('seo:generate', ['--base-url' => 'https://thinkerhub.online'])
            ->assertExitCode(0);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $this->get('/sitemap-static.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $this->get('/sitemap-courses.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $coursesXml = file_get_contents(public_path('sitemap-courses.xml'));
        $this->assertStringContainsString('cloud-engineering-bootcamp', (string) $coursesXml);
    }

    public function test_course_promo_badge_and_scrolling_sticky_top_banner_render(): void
    {
        $promoCourse = Course::create([
            'title' => 'AI & Machine Learning Intensive',
            'code' => 'AI-301',
            'overview' => 'Hands-on AI engineering course.',
            'fees' => '2000',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        $regularCourse = Course::create([
            'title' => 'Regular Web Course',
            'code' => 'WEB-102',
            'overview' => 'Regular web course without promo.',
            'fees' => '1500',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        \App\Models\PromoCode::create([
            'code' => 'AIBOOST30',
            'description' => '30% off AI Intensive',
            'discount_type' => 'percentage',
            'discount_value' => 30,
            'course_id' => $promoCourse->id,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->assertNotNull($promoCourse->getPromoBadge());
        $this->assertSame('PROMO • 30% OFF', $promoCourse->getPromoBadge()['label']);
        $this->assertNull($regularCourse->getPromoBadge());

        $response = $this->get('/courses');
        $response->assertOk();
        $response->assertSee('hub-top-ticker', false);
        $response->assertSee('PROMO • 30% OFF');
        $response->assertSee('CODE: AIBOOST30');

        // Clicking the promo in the banner filters /courses?promo=AIBOOST30 to only the respective course
        $filteredResponse = $this->get('/courses?promo=AIBOOST30');
        $filteredResponse->assertOk();
        $filteredResponse->assertSee('AI &amp; Machine Learning Intensive', false);
        $filteredResponse->assertDontSee('Regular Web Course');
    }
}

