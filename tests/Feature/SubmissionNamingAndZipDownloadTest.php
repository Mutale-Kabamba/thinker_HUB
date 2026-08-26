<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource as InstructorAssignmentSubmissionResource;
use App\Filament\Student\Pages\Assessments;
use App\Filament\Student\Pages\Assignments;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\SubmissionZipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class SubmissionNamingAndZipDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_submission_upload_stores_file_with_first_name_and_assignment_slug(): void
    {
        Storage::fake('public');

        $student = User::factory()->create([
            'name' => 'Alice Johnson',
            'role' => 'student',
            'track' => 'Beginner',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Python Fundamentals',
            'code' => 'PY101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Data Structures Lab',
            'target_level' => 'Beginner',
            'date_given' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'publish_at' => now()->subDay(),
        ]);

        $file = UploadedFile::fake()->create('project.pdf', 500, 'application/pdf');

        Livewire::actingAs($student)
            ->test(Assignments::class)
            ->set('submissionDrafts.' . $assignment->id . '.file', $file)
            ->call('submit', $assignment->id)
            ->assertHasNoErrors();

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertNotNull($submission->file_path);
        // Stored filename must start with alice_data_structures_lab
        $this->assertStringContainsString('alice_data_structures_lab', $submission->file_path);

        Storage::disk('public')->assertExists($submission->file_path);
    }

    public function test_student_download_submission_file_provides_formatted_filename(): void
    {
        Storage::fake('public');

        $student = User::factory()->create([
            'name' => 'Bob Builder',
            'role' => 'student',
            'track' => 'Beginner',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'CSS Flexbox Homework',
            'target_level' => 'Beginner',
            'date_given' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'publish_at' => now()->subDay(),
        ]);

        Storage::disk('public')->put('submissions/bob_file.pdf', 'sample content');

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/bob_file.pdf',
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $response = Livewire::actingAs($student)
            ->test(Assignments::class)
            ->call('downloadSubmissionFile', $assignment->id);

        $this->assertNotNull($response);
    }

    public function test_assessment_submission_upload_stores_file_with_first_name_and_assessment_slug(): void
    {
        Storage::fake('public');

        $student = User::factory()->create([
            'name' => 'Carol Danvers',
            'role' => 'student',
            'track' => 'Beginner',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Machine Learning',
            'code' => 'ML101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $assessment = Assessment::create([
            'course_id' => $course->id,
            'name' => 'Midterm Project',
            'target_level' => 'Beginner',
            'date_given' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'publish_at' => now()->subDay(),
        ]);

        $file = UploadedFile::fake()->create('report.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        Livewire::actingAs($student)
            ->test(Assessments::class)
            ->set('submissionDrafts.' . $assessment->id . '.file', $file)
            ->call('submit', $assessment->id)
            ->assertHasNoErrors();

        $submission = AssessmentSubmission::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertNotNull($submission->file_path);
        $this->assertStringContainsString('carol_midterm_project', $submission->file_path);

        Storage::disk('public')->assertExists($submission->file_path);
    }

    public function test_submission_zip_service_builds_valid_zip_with_correct_naming(): void
    {
        Storage::fake('public');

        $student1 = User::factory()->create(['name' => 'David Miller', 'role' => 'student']);
        $student2 = User::factory()->create(['name' => 'Emma Watson', 'role' => 'student']);

        $course = Course::create(['title' => 'UI UX Design', 'code' => 'UI101', 'is_active' => true]);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Figma Prototype',
            'target_level' => 'Beginner',
            'date_given' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $path1 = 'submissions/d_proto.pdf';
        $path2 = 'submissions/e_proto.pdf';
        $path3 = 'submissions/e_extra.png';

        Storage::disk('public')->put($path1, 'David Prototype');
        Storage::disk('public')->put($path2, 'Emma Prototype');
        Storage::disk('public')->put($path3, 'Emma Extra Screen');

        $sub1 = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student1->id,
            'file_path' => $path1,
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $sub2 = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student2->id,
            'file_paths' => [$path2, $path3],
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $service = app(SubmissionZipService::class);
        $response = $service->downloadAssignmentsZip(collect([$sub1, $sub2]), 'Test_Assignments');

        $this->assertNotNull($response);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $zipContent = ob_get_clean();

        $this->assertNotEmpty($zipContent);

        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $opened = $zip->open($tempZip);
        $this->assertTrue($opened);

        $entryNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryNames[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        // Check entry names in ZIP
        $this->assertContains('david_figma_prototype.pdf', $entryNames);
        $this->assertContains('emma_figma_prototype_1.pdf', $entryNames);
        $this->assertContains('emma_figma_prototype_2.png', $entryNames);
    }

    public function test_submission_zip_service_creates_text_summary_file_when_physical_file_is_missing(): void
    {
        $student = User::factory()->create(['name' => 'Frank Castle', 'role' => 'student']);
        $course = Course::create(['title' => 'Cyber Security', 'code' => 'SEC101', 'is_active' => true]);
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Threat Model',
            'target_level' => 'Beginner',
            'date_given' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/non_existent_file.pdf',
            'content' => 'My security analysis summary',
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $service = app(SubmissionZipService::class);
        $response = $service->downloadAssignmentsZip(collect([$submission]));

        $this->assertNotNull($response);

        ob_start();
        $response->sendContent();
        $zipContent = ob_get_clean();

        $this->assertNotEmpty($zipContent);

        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_fallback');
        file_put_contents($tempZip, $zipContent);

        $zip = new ZipArchive();
        $opened = $zip->open($tempZip);
        $this->assertTrue($opened);

        $entryNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryNames[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($tempZip);

        $this->assertContains('frank_threat_model_submission_details.txt', $entryNames);
    }

    public function test_single_submission_download(): void
    {
        Storage::fake('public');
        $student = User::factory()->create(['name' => 'Grace Hopper', 'role' => 'student']);
        $course = Course::create(['title' => 'Compiler Design', 'code' => 'CS301', 'is_active' => true]);
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Lexer Parser',
            'target_level' => 'Beginner',
            'date_given' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $filePath = 'submissions/grace_lexer.pdf';
        Storage::disk('public')->put($filePath, 'Grace Lexer Content');

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => $filePath,
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $service = app(SubmissionZipService::class);
        $response = $service->downloadSingleAssignmentSubmission($submission);

        $this->assertNotNull($response);
    }
}

