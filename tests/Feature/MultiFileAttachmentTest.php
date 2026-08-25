<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiFileAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_multi_file_support_and_backward_compatibility(): void
    {
        Storage::fake('public');

        // Create assignment with multiple files
        $assignment = Assignment::create([
            'name' => 'Multi File Assignment',
            'file_paths' => ['assignments/file1.pdf', 'assignments/file2.docx'],
            'scope' => 'all',
            'status' => 'published',
        ]);

        $this->assertCount(2, $assignment->all_file_paths);
        $this->assertEquals('assignments/file1.pdf', $assignment->file_path);
        $this->assertEquals(['assignments/file1.pdf', 'assignments/file2.docx'], $assignment->file_paths);

        // Legacy assignment with only single file_path
        $legacyAssignment = Assignment::create([
            'name' => 'Legacy Assignment',
            'file_path' => 'assignments/legacy.pdf',
            'scope' => 'all',
            'status' => 'published',
        ]);

        $this->assertCount(1, $legacyAssignment->all_file_paths);
        $this->assertEquals(['assignments/legacy.pdf'], $legacyAssignment->all_file_paths);
    }

    public function test_assessment_multi_file_support_and_backward_compatibility(): void
    {
        Storage::fake('public');

        $instructor = User::factory()->create(['role' => 'instructor']);

        $assessment = Assessment::create([
            'user_id' => $instructor->id,
            'name' => 'Multi File Assessment',
            'file_paths' => ['assessments/test1.pdf', 'assessments/test2.pdf'],
            'scope' => 'all',
            'status' => 'published',
        ]);

        $this->assertCount(2, $assessment->all_file_paths);
        $this->assertEquals('assessments/test1.pdf', $assessment->file_path);

        $legacyAssessment = Assessment::create([
            'user_id' => $instructor->id,
            'name' => 'Legacy Assessment',
            'file_path' => 'assessments/legacy.pdf',
            'scope' => 'all',
            'status' => 'published',
        ]);

        $this->assertCount(1, $legacyAssessment->all_file_paths);
        $this->assertEquals(['assessments/legacy.pdf'], $legacyAssessment->all_file_paths);
    }

    public function test_assignment_and_assessment_submission_multi_files(): void
    {
        Storage::fake('public');

        $instructor = User::factory()->create(['role' => 'instructor']);
        $user = User::factory()->create(['role' => 'student']);
        $assignment = Assignment::create([
            'name' => 'Test Assignment',
            'scope' => 'all',
            'status' => 'published',
        ]);

        $sub = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'file_paths' => ['submissions/part1.pdf', 'submissions/part2.zip'],
            'status' => 'Submitted',
        ]);

        $this->assertCount(2, $sub->all_file_paths);
        $this->assertEquals('submissions/part1.pdf', $sub->file_path);

        $assessment = Assessment::create([
            'user_id' => $instructor->id,
            'name' => 'Test Assessment',
            'scope' => 'all',
            'status' => 'published',
        ]);

        $assessSub = AssessmentSubmission::create([
            'assessment_id' => $assessment->id,
            'user_id' => $user->id,
            'file_paths' => ['submissions/eval1.pdf', 'submissions/eval2.pdf'],
            'status' => 'Submitted',
        ]);

        $this->assertCount(2, $assessSub->all_file_paths);
        $this->assertEquals('submissions/eval1.pdf', $assessSub->file_path);
    }

    public function test_chat_message_multi_attachments(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $room = ChatRoom::create(['type' => 'general', 'name' => 'General Chat']);

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'body' => 'Check out these files',
            'attachments' => [
                ['path' => 'chat-attachments/img1.png', 'name' => 'img1.png', 'type' => 'image'],
                ['path' => 'chat-attachments/doc1.pdf', 'name' => 'doc1.pdf', 'type' => 'file'],
            ],
        ]);

        $this->assertCount(2, $message->all_attachments);
        $this->assertEquals('chat-attachments/img1.png', $message->attachment_path);
        $this->assertEquals('img1.png', $message->attachment_name);
        $this->assertEquals('image', $message->attachment_type);

        // Legacy message with single attachment
        $legacyMessage = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'body' => 'Legacy attachment message',
            'attachment_path' => 'chat-attachments/old.pdf',
            'attachment_name' => 'old.pdf',
            'attachment_type' => 'file',
        ]);

        $this->assertCount(1, $legacyMessage->all_attachments);
        $this->assertEquals('old.pdf', $legacyMessage->all_attachments[0]['name']);
    }

    public function test_file_view_route_multi_file_index_support(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('assignments/file1.pdf', 'content1');
        Storage::disk('public')->put('assignments/file2.pdf', 'content2');

        $admin = User::factory()->create(['role' => 'admin']);
        $assignment = Assignment::create([
            'name' => 'Course Assignment',
            'file_paths' => ['assignments/file1.pdf', 'assignments/file2.pdf'],
            'scope' => 'all',
            'status' => 'published',
        ]);

        $this->actingAs($admin);

        // Access index 0
        $response0 = $this->get(route('file.view', ['type' => 'assignment', 'id' => $assignment->id, 'index' => 0]));
        $response0->assertStatus(200);

        // Access index 1
        $response1 = $this->get(route('file.view', ['type' => 'assignment', 'id' => $assignment->id, 'index' => 1]));
        $response1->assertStatus(200);
    }
}
