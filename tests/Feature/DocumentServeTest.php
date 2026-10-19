<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\Media;
use App\Models\User;
use App\Support\DocumentResponse;
use App\Support\PublicDiskPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentServeTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $instructor;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::create([
            'title' => 'Test Course',
            'code' => 'TC101',
            'is_active' => true,
        ]);

        $this->instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->course->students()->attach($this->student->id);
    }

    public function test_view_existing_document_streams_file_with_safe_headers(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('materials/sample_lecture.pdf', '%PDF-1.4 sample pdf content');

        $material = LearningMaterial::create([
            'course_id' => $this->course->id,
            'title' => 'Sample Lecture',
            'file_path' => 'materials/sample_lecture.pdf',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('file.view', ['type' => 'material', 'id' => $material->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    public function test_download_existing_document_sets_attachment_disposition(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('materials/sample_handout.docx', 'docx binary content');

        $material = LearningMaterial::create([
            'course_id' => $this->course->id,
            'title' => 'Sample Handout',
            'file_path' => 'materials/sample_handout.docx',
            'file_name' => 'CustomHandoutName.docx',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('file.download', ['type' => 'material', 'id' => $material->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('CustomHandoutName.docx', $response->headers->get('Content-Disposition'));
    }

    public function test_view_missing_document_returns_404_html_not_500(): void
    {
        Storage::fake('public');

        $material = LearningMaterial::create([
            'course_id' => $this->course->id,
            'title' => 'Missing Lecture File',
            'file_path' => 'materials/does_not_exist.pdf',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('file.view', ['type' => 'material', 'id' => $material->id]));

        $response->assertStatus(404);
        $this->assertStringContainsString('File not found', $response->getContent());
    }

    public function test_download_missing_document_returns_404_not_500(): void
    {
        Storage::fake('public');

        $material = LearningMaterial::create([
            'course_id' => $this->course->id,
            'title' => 'Missing Handout',
            'file_path' => 'materials/missing.pdf',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('file.download', ['type' => 'material', 'id' => $material->id]));

        $response->assertStatus(404);
    }

    public function test_multi_disk_resolution_finds_file_on_alternate_disk(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        // File only exists on 'local' disk:
        Storage::disk('local')->put('materials/local_notes.txt', 'Local notes content');

        $resolved = DocumentResponse::resolveFile('materials/local_notes.txt');

        $this->assertNotNull($resolved[0], 'Expected disk to be resolved');
        $this->assertEquals('materials/local_notes.txt', $resolved[1]);
        $this->assertEquals('local', $resolved[2]);
    }

    public function test_document_response_serves_without_crashing_when_remote_disk_throws(): void
    {
        // Configure a broken disk that throws Flysystem exceptions
        config(['filesystems.disks.broken_cloud' => [
            'driver' => 's3',
            'key' => 'invalid-key',
            'secret' => 'invalid-secret',
            'region' => 'auto',
            'bucket' => 'non-existent-bucket',
            'throw' => true,
        ]]);

        $response = DocumentResponse::serve(
            rawPath: 'non_existent_cloud_file.pdf',
            downloadName: 'file.pdf',
            isDownload: false,
            preferredDisk: 'broken_cloud'
        );

        // Must gracefully return 404 response without throwing 500
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_public_disk_path_url_does_not_crash_when_storage_throws(): void
    {
        config(['filesystems.disks.public.driver' => 's3']);
        config(['filesystems.disks.public.key' => null]);

        $url = PublicDiskPath::url('avatars/student.png');

        $this->assertNotNull($url);
        $this->assertStringContainsString('avatars/student.png', $url);
    }

    public function test_media_download_route_uses_safe_document_response(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/test_media.jpg', 'fake-image-bytes');

        $media = Media::create([
            'mediable_type' => Course::class,
            'mediable_id' => $this->course->id,
            'disk' => 'public',
            'path' => 'uploads/test_media.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 16,
            'status' => 'ready',
        ]);

        $response = $this->get(route('media.download', ['media' => $media->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('photo.jpg', $response->headers->get('Content-Disposition'));
    }

    public function test_filesystems_config_supports_laravel_cloud_disk_config(): void
    {
        // Simulate Laravel Cloud environment with injected disk configuration
        $_SERVER['LARAVEL_CLOUD_DISK_CONFIG'] = json_encode([
            [
                'disk' => 'cloud-bucket',
                'access_key_id' => 'test-key',
                'access_key_secret' => 'test-secret',
                'region' => 'auto',
                'bucket' => 'thinker-prod',
                'url' => 'https://pub-r2.thinker.it.com',
                'endpoint' => 'https://r2.cloudflarestorage.com/tenant',
                'is_default' => true,
            ]
        ]);

        $config = require config_path('filesystems.php');

        unset($_SERVER['LARAVEL_CLOUD_DISK_CONFIG']);

        $this->assertEquals('cloud-bucket', $config['default']);
        $this->assertArrayHasKey('cloud-bucket', $config['disks']);
        $this->assertEquals('https://r2.cloudflarestorage.com/tenant', $config['disks']['cloud-bucket']['endpoint']);
        $this->assertEquals('auto', $config['disks']['cloud-bucket']['region']);
        $this->assertEquals('thinker-prod', $config['disks']['public']['bucket']);
    }

    public function test_admin_can_access_storage_check(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.storage-check'));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'CONNECTED_AND_WORKING',
            'operations' => [
                'write' => true,
                'read' => true,
                'delete' => true,
            ],
        ]);
    }

    public function test_non_admin_cannot_access_storage_check(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('admin.storage-check'));

        $response->assertStatus(403);
    }
}
