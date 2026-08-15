<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaAndMobileAppExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pwa_manifest_is_accessible_and_valid(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $json = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($json);
        $this->assertSame('think.er HUB', $json['name']);
        $this->assertSame('ThinkerHUB', $json['short_name']);
        $this->assertSame('standalone', $json['display']);
        $this->assertSame('#006a67', $json['theme_color']);
        $this->assertSame('#07191e', $json['background_color']);
        $this->assertNotEmpty($json['icons']);
        $this->assertNotEmpty($json['shortcuts']);
    }

    public function test_pwa_offline_page_is_accessible_and_branded(): void
    {
        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertStringContainsString('think.er HUB', $content);
        $this->assertStringContainsString('Offline Workspace', $content);
        $this->assertStringContainsString('Reconnect Now', $content);
    }

    public function test_pwa_service_worker_is_accessible_and_configured(): void
    {
        $swPath = public_path('service-worker.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);
        $this->assertStringContainsString('thinkerhub-v3', $content);
        $this->assertStringContainsString('/offline.html', $content);
        $this->assertStringContainsString('/manifest.json', $content);
    }

    public function test_guest_layout_renders_pwa_and_preloader(): void
    {
        $response = $this->get('/login');
        $response->assertSuccessful();
        $response->assertSee('manifest.json');
        $response->assertSee('thinker-app-preloader');
        $response->assertSee('viewport-fit=cover');
    }

    public function test_student_portal_renders_pwa_and_preloader(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/learn/claim-hub');
        $response->assertSuccessful();
        $response->assertSee('manifest.json');
        $response->assertSee('thinker-app-preloader');
    }
}
