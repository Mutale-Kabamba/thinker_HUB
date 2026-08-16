<?php

namespace Tests\Feature;

use App\Jobs\GenerateMaterialThumbnailJob;
use App\Jobs\SendMaterialNotificationJob;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\User;
use App\Notifications\AssignmentAssignedNotification;
use App\Notifications\MaterialPublishedNotification;
use App\Notifications\QuizPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AsyncMaterialUploadProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_creation_dispatches_background_jobs_to_prevent_timeout(): void
    {
        Queue::fake();

        $course = Course::create([
            'title' => 'Cloud Infrastructure',
            'code' => 'DEV901',
            'is_active' => true,
        ]);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Microservices and Queues Guide',
            'category' => 'Study Material',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'microservices.pdf',
            'file_path' => 'materials/microservices.pdf',
        ]);

        Queue::assertPushed(SendMaterialNotificationJob::class, function (SendMaterialNotificationJob $job) use ($material) {
            return $job->material->id === $material->id;
        });

        Queue::assertPushed(GenerateMaterialThumbnailJob::class, function (GenerateMaterialThumbnailJob $job) use ($material) {
            return $job->material->id === $material->id;
        });
    }

    public function test_send_material_notification_job_notifies_enrolled_students(): void
    {
        Notification::fake();

        $course = Course::create([
            'title' => 'Backend Systems',
            'code' => 'CS505',
            'is_active' => true,
        ]);

        $student1 = User::factory()->create(['role' => 'student', 'email' => 'student1@example.com']);
        $student2 = User::factory()->create(['role' => 'student', 'email' => 'student2@example.com']);
        $unrelatedStudent = User::factory()->create(['role' => 'student', 'email' => 'other@example.com']);

        Enrollment::create(['user_id' => $student1->id, 'course_id' => $course->id, 'status' => 'enrolled']);
        Enrollment::create(['user_id' => $student2->id, 'course_id' => $course->id, 'status' => 'enrolled']);

        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Database Queues and Optimization',
            'category' => 'Study Material',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'queues.pdf',
            'file_path' => 'materials/queues.pdf',
        ]);

        $job = new SendMaterialNotificationJob($material);
        $job->handle();

        Notification::assertSentTo([$student1, $student2], MaterialPublishedNotification::class);
        Notification::assertNotSentTo([$unrelatedStudent], MaterialPublishedNotification::class);
    }

    public function test_generate_material_thumbnail_job_handles_missing_or_valid_files_gracefully(): void
    {
        $material = LearningMaterial::create([
            'title' => 'Async PDF Guide',
            'category' => 'Study Material',
            'material_type' => 'Document',
            'scope' => 'all',
            'file_name' => 'async_guide.pdf',
            'file_path' => 'materials/async_guide.pdf',
        ]);

        $job = new GenerateMaterialThumbnailJob($material);
        $job->handle();

        $this->assertTrue(true);
    }

    public function test_notification_classes_implement_should_queue(): void
    {
        $this->assertTrue(
            is_subclass_of(MaterialPublishedNotification::class, ShouldQueue::class),
            'MaterialPublishedNotification must implement ShouldQueue.'
        );

        $this->assertTrue(
            is_subclass_of(AssignmentAssignedNotification::class, ShouldQueue::class),
            'AssignmentAssignedNotification must implement ShouldQueue.'
        );

        $this->assertTrue(
            is_subclass_of(QuizPublishedNotification::class, ShouldQueue::class),
            'QuizPublishedNotification must implement ShouldQueue.'
        );
    }
}
