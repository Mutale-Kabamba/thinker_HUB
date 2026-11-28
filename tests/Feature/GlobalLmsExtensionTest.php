<?php

namespace Tests\Feature;

use App\Filament\Pages\RegionalAnalytics;
use App\Livewire\Student\ContinueLearningWidget;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\ResourceDownload;
use App\Models\ScormPackage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtmAttribution;
use App\Services\AnalyticsExportService;
use App\Services\RegionalAnalyticsService;
use App\Services\ScormPackageService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class GlobalLmsExtensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_1_global_multi_tenancy_and_null_backward_compatibility(): void
    {
        $tenantZm = Tenant::create([
            'name' => 'Zambia Digital Skills',
            'country_code' => 'ZM',
            'country_name' => 'Zambia',
            'timezone' => 'Africa/Lusaka',
            'currency' => 'ZMW',
        ]);

        $tenantUk = Tenant::create([
            'name' => 'UK Global Fellowship',
            'country_code' => 'GB',
            'country_name' => 'United Kingdom',
            'timezone' => 'Europe/London',
            'currency' => 'GBP',
        ]);

        // Legacy/global course with tenant_id = NULL continues working seamlessly
        $globalCourse = Course::create(['title' => 'Global Intro', 'code' => 'GLB-101', 'is_active' => true]);
        $zmCourse = Course::create(['title' => 'Zambia Web Track', 'code' => 'ZM-101', 'tenant_id' => $tenantZm->id, 'is_active' => true]);
        $ukCourse = Course::create(['title' => 'UK Cloud Track', 'code' => 'UK-101', 'tenant_id' => $tenantUk->id, 'is_active' => true]);

        $programAdmin = User::factory()->create([
            'role' => User::ROLE_PROGRAM_ADMIN,
            'tenant_id' => $tenantZm->id,
            'is_active' => true,
        ]);

        $this->assertTrue($programAdmin->isProgramAdmin());
        $this->assertSame($tenantZm->id, $programAdmin->managedTenantId());
        $this->assertTrue($programAdmin->canAccessPanel(Filament::getPanel('admin')));

        $scopedIds = Course::query()->visibleToAdministrator($programAdmin)->pluck('id')->all();
        $this->assertSame([$zmCourse->id], $scopedIds);

        // Null tenant_id records remain untouched and accessible globally
        $this->assertNull($globalCourse->tenant_id);
        $this->assertCount(3, Course::query()->forTenant(null)->get());
        $this->assertNotContains($ukCourse->id, $scopedIds);
    }

    public function test_phase_2_utm_capture_middleware_and_enrollment_attribution(): void
    {
        $response = $this->get('/?utm_source=tiktok&utm_medium=video&utm_campaign=africa_launch');
        $response->assertCookie('thinker_utm');

        $tenant = Tenant::create(['name' => 'Malawi Youth Program', 'country_code' => 'MW', 'country_name' => 'Malawi']);
        $course = Course::create(['title' => 'Python Starter', 'code' => 'PY-100', 'tenant_id' => $tenant->id, 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'tenant_id' => $tenant->id, 'is_active' => true]);

        // Simulate request carrying UTM session payload when enrollment is created
        session(['thinker_utm' => [
            'utm_source' => 'tiktok',
            'utm_medium' => 'video',
            'utm_campaign' => 'africa_launch',
            'captured_at' => now()->toIso8601String(),
        ]]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'tenant_id' => $tenant->id,
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        $this->assertDatabaseHas('utm_attributions', [
            'user_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'tenant_id' => $tenant->id,
            'utm_source' => 'tiktok',
            'utm_campaign' => 'africa_launch',
        ]);
    }

    public function test_phase_3_scorm_zip_import_cmi_commit_xapi_and_resource_download_tracking(): void
    {
        Storage::fake('public');

        $course = Course::create(['title' => 'Interactive SCORM Course', 'code' => 'SCM-101', 'is_active' => true]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'SCORM Module 1',
            'type' => 'scorm',
            'order_column' => 1,
            'resource_files' => [['name' => 'Workbook.pdf', 'path' => 'packets/workbook.pdf']],
        ]);
        Storage::disk('public')->put('packets/workbook.pdf', 'PDF-DATA');

        // Build a valid SCORM 1.2 zip in temp directory
        $zipPath = tempnam(sys_get_temp_dir(), 'scorm_test_') . '.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('imsmanifest.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<manifest identifier="com.thinkerhub.scorm12">
  <organizations default="org1">
    <organization identifier="org1">
      <title>Digital Literacy 101</title>
    </organization>
  </organizations>
  <resources>
    <resource identifier="res1" type="webcontent" href="index.html" />
  </resources>
</manifest>
XML);
        $zip->addFromString('index.html', '<html><body>SCORM Content</body></html>');
        $zip->close();

        $service = app(ScormPackageService::class);
        $package = $service->importZip($zipPath, ['course_id' => $course->id, 'lesson_id' => $lesson->id]);
        @unlink($zipPath);

        $this->assertSame(ScormPackage::STANDARD_SCORM_12, $package->standard);
        $this->assertSame('Digital Literacy 101', $package->title);
        $this->assertSame('index.html', $package->entry_point);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $attempt = $service->startOrResumeAttempt($package, $student, $lesson);

        // Commit SCORM CMI data via runtime endpoint
        $this->actingAs($student)
            ->postJson(route('scorm.commit', $attempt), [
                'cmi' => [
                    'cmi.core.lesson_status' => 'passed',
                    'cmi.core.score.raw' => '94',
                    'cmi.core.lesson_location' => 'slide_08',
                    'cmi.core.session_time' => '00:12:30',
                ],
            ])
            ->assertOk()
            ->assertJson([
                'lesson_status' => 'passed',
                'completed' => true,
            ]);

        $attempt->refresh();
        $this->assertEquals(94.0, (float) $attempt->score_raw);
        $this->assertSame('slide_08', $attempt->lesson_location);
        $this->assertNotNull($attempt->completed_at);

        // Test xAPI statement ingestion
        $this->actingAs($student)
            ->postJson(route('scorm.xapi', $attempt), [
                'verb' => ['id' => 'http://adlnet.gov/expapi/verbs/completed'],
                'result' => ['score' => ['raw' => 98, 'scaled' => 0.98]],
            ])
            ->assertOk()
            ->assertJson(['lesson_status' => 'completed', 'completed' => true]);

        // Test tracked resource download
        $this->actingAs($student)
            ->get(route('lessons.resource.download', ['lesson' => $lesson->id, 'index' => 0]))
            ->assertOk();

        $this->assertDatabaseHas('resource_downloads', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'file_name' => 'Workbook.pdf',
        ]);
    }

    public function test_phase_4_learner_widget_regional_analytics_and_csv_xlsx_exports(): void
    {
        $tenant = Tenant::create(['name' => 'Namibia Cohort', 'country_code' => 'NA', 'country_name' => 'Namibia']);
        $course = Course::create(['title' => 'Data Analytics', 'code' => 'DAT-101', 'tenant_id' => $tenant->id, 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'tenant_id' => $tenant->id, 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'tenant_id' => $tenant->id,
            'progress_percentage' => 65,
        ]);

        UtmAttribution::create([
            'user_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'tenant_id' => $tenant->id,
            'utm_source' => 'whatsapp',
            'utm_campaign' => 'namibia_cohort_1',
            'captured_at' => now(),
        ]);

        // 1. Learner Continue Learning Widget renders cleanly
        Livewire::actingAs($student)
            ->test(ContinueLearningWidget::class)
            ->assertOk()
            ->assertSee('Data Analytics')
            ->assertSee('65%');

        // 2. Analytics KPIs & UTM breakdown
        $summary = app(RegionalAnalyticsService::class)->summary(['tenant_id' => $tenant->id], $admin);
        $this->assertSame(1, $summary['kpis']['total_registrations']);
        $this->assertSame('whatsapp', $summary['utm_breakdown'][0]['source']);

        // 3. CSV and native XLSX exports succeed
        $exportService = app(AnalyticsExportService::class);
        $csvResponse = $exportService->download('utm_attribution', 'csv', ['tenant_id' => $tenant->id], $admin);
        $xlsxResponse = $exportService->download('learner_progress', 'xlsx', ['tenant_id' => $tenant->id], $admin);

        $this->assertSame(200, $csvResponse->getStatusCode());
        $this->assertSame(200, $xlsxResponse->getStatusCode());
    }
}
