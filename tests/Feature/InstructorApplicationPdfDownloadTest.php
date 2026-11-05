<?php

namespace Tests\Feature;

use App\Filament\Resources\InstructorApplications\Pages\EditInstructorApplication;
use App\Filament\Resources\InstructorApplications\Pages\ListInstructorApplications;
use App\Models\Course;
use App\Models\InstructorApplication;
use App\Models\User;
use App\Services\InstructorApplicationPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InstructorApplicationPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_service_renders_valid_pdf_for_existing_course_proposal(): void
    {
        $course = Course::create([
            'title' => 'Python Data Science Masterclass',
            'code' => 'PY-501',
            'is_active' => true,
        ]);

        $reviewer = User::factory()->create([
            'name' => 'Dean Administrator',
            'email' => 'dean@thinker.test',
            'role' => 'admin',
        ]);

        $applicant = User::factory()->create([
            'name' => 'Dr. Jane Mwape',
            'email' => 'jane.mwape@thinker.test',
            'role' => 'instructor',
            'is_active' => false,
        ]);

        $application = InstructorApplication::create([
            'name' => 'Dr. Jane Mwape',
            'email' => 'jane.mwape@thinker.test',
            'phone' => '+260 971 123456',
            'whatsapp' => '+260 971 123456',
            'bio' => 'Senior Machine Learning Researcher with 10 years experience in AI training and curriculum delivery.',
            'proficiency' => 'Artificial Intelligence & Machine Learning',
            'occupation' => 'Lead Data Scientist',
            'qualifications' => "PhD in Computer Science (Univ of Edinburgh)\nBSc in Information Systems (UNZA)",
            'experience' => "5 years adjunct lecturing at national universities.\nDelivered over 20 bootcamps in Python & PyTorch.",
            'linkedin_url' => 'https://linkedin.com/in/janemwape',
            'portfolio_url' => 'https://janemwape.ai',
            'proposal_type' => 'existing',
            'preferred_course_id' => $course->id,
            'motivation_note' => 'I have followed Thinker HUB\'s track record and want to expand the data curriculum.',
            'competence_note' => 'Expert in Python, Pandas, NumPy, Scikit-Learn, and Deep Learning.',
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => 'Highly qualified candidate with strong academic and industry alignment.',
            'user_id' => $applicant->id,
        ]);

        $service = app(InstructorApplicationPdfService::class);
        $pdf = $service->renderApplicationPdf($application);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);

        $data = $service->prepareApplicationData($application);
        $this->assertEquals('Dr. Jane Mwape', $data['applicant']['name']);
        $this->assertEquals('existing', $data['proposal']['type']);
        $this->assertEquals('Approved', $data['review']['status_label']);
        $this->assertEquals('Dean Administrator', $data['review']['reviewer_name']);
        $this->assertEquals('Python Data Science Masterclass', $data['proposal']['preferred_course']->title);
    }

    public function test_pdf_service_renders_valid_pdf_for_new_course_proposal_with_fees_and_progression(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('instructor-cvs/test_cv.pdf', 'dummy-pdf-content');

        $application = InstructorApplication::create([
            'name' => 'Marcus Phiri',
            'email' => 'marcus.phiri@thinker.test',
            'phone' => '+260 965 987654',
            'whatsapp' => '+260 965 987654',
            'bio' => 'Senior DevOps and Cloud Infrastructure architect.',
            'proficiency' => 'Cloud Native DevOps & Kubernetes',
            'occupation' => 'Principal Cloud Engineer',
            'qualifications' => "AWS Certified Solutions Architect Professional\nCKA (Certified Kubernetes Administrator)",
            'experience' => '8 years managing distributed cloud infrastructure on AWS and GCP.',
            'proposal_type' => 'new',
            'proposed_course_name' => 'Production Kubernetes & Cloud Engineering',
            'proposed_course_code' => 'K8S-401',
            'teaching_location' => 'Online & Hybrid Lusaka Tech Hub',
            'proposed_course_timeline' => '12 Weeks Intensive',
            'proposed_course_is_open_enrollment' => true,
            'proposed_course_overview' => 'From containerization fundamentals to multi-cluster orchestration and GitOps.',
            'proposed_course_description' => 'Comprehensive real-world engineering laboratory covering Kubernetes, Terraform, Prometheus, and CI/CD.',
            'proposed_course_key_outcome' => 'Learners will deploy and manage high-availability clusters with zero-downtime deployments.',
            'proposed_course_requirements' => "Basic Linux CLI familiarity\nFundamental understanding of networking (TCP/IP, DNS)",
            'proposed_course_fees' => "group + beginner + ZMW 3,500 + 4 Weeks\ngroup + intermediate + ZMW 5,500 + 4 Weeks\none_on_one + advanced + ZMW 8,500 + 4 Weeks",
            'proposed_course_level_progression' => "Beginner + Docker, Container Networking, Helm\nIntermediate + Ingress, ConfigMaps, StatefulSets\nAdvanced + Service Mesh, Multi-Cluster, GitOps with ArgoCD",
            'cv_path' => 'instructor-cvs/test_cv.pdf',
            'status' => 'pending',
        ]);

        $service = app(InstructorApplicationPdfService::class);
        $pdf = $service->renderApplicationPdf($application);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);

        $data = $service->prepareApplicationData($application);
        $this->assertEquals('new', $data['proposal']['type']);
        $this->assertEquals('Pending Review', $data['review']['status_label']);
        $this->assertCount(3, $data['proposal']['fees_structured']);
        $this->assertCount(3, $data['proposal']['progression_structured']);
        $this->assertTrue($data['attachments']['cv']['attached']);
        $this->assertTrue($data['attachments']['cv']['exists_on_disk']);
    }

    public function test_pdf_service_renders_batch_consolidated_dossier(): void
    {
        $app1 = InstructorApplication::create([
            'name' => 'Applicant One',
            'email' => 'one@thinker.test',
            'qualifications' => 'BSc Computer Science',
            'proposal_type' => 'existing',
            'status' => 'pending',
        ]);

        $app2 = InstructorApplication::create([
            'name' => 'Applicant Two',
            'email' => 'two@thinker.test',
            'qualifications' => 'MSc Data Science',
            'proposal_type' => 'new',
            'proposed_course_name' => 'Applied Machine Learning',
            'status' => 'approved',
        ]);

        $service = app(InstructorApplicationPdfService::class);
        $pdf = $service->renderCombinedApplicationsPdf(collect([$app1, $app2]));
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_admin_can_download_instructor_application_pdf_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $application = InstructorApplication::create([
            'name' => 'Chileshe Kapembwa',
            'email' => 'chileshe@thinker.test',
            'qualifications' => 'BSc Electrical Engineering',
            'proposal_type' => 'new',
            'proposed_course_name' => 'IoT and Embedded Robotics',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('reports.instructor-application', [
            'application' => $application->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Instructor_Application_chileshe_kapembwa', (string) $response->headers->get('content-disposition'));
    }

    public function test_admin_can_stream_instructor_application_pdf_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $application = InstructorApplication::create([
            'name' => 'Sipho Zulu',
            'email' => 'sipho@thinker.test',
            'qualifications' => 'Fullstack Web Engineer',
            'proposal_type' => 'existing',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('reports.instructor-application', [
            'application' => $application->id,
            'stream' => 1,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
    }

    public function test_unauthorized_user_cannot_download_instructor_application_pdf(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $application = InstructorApplication::create([
            'name' => 'Private Applicant',
            'email' => 'private@thinker.test',
            'qualifications' => 'Sensitive credentials',
            'status' => 'pending',
        ]);

        // Student attempt
        $response = $this->actingAs($student)->get(route('reports.instructor-application', [
            'application' => $application->id,
        ]));
        $response->assertForbidden();

        // Guest attempt
        auth()->logout();
        $guestResponse = $this->get(route('reports.instructor-application', [
            'application' => $application->id,
        ]));
        $guestResponse->assertRedirect(route('login'));
    }

    public function test_filament_instructor_applications_table_has_download_pdf_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $application = InstructorApplication::create([
            'name' => 'Musonda Tembo',
            'email' => 'musonda@thinker.test',
            'qualifications' => 'Certified Scrum Master & Agile Coach',
            'proposal_type' => 'existing',
            'status' => 'pending',
        ]);

        Livewire::test(ListInstructorApplications::class)
            ->assertSuccessful()
            ->assertSee('Musonda Tembo')
            ->assertTableActionExists('download_pdf');
    }

    public function test_filament_edit_instructor_application_page_has_download_pdf_header_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $application = InstructorApplication::create([
            'name' => 'Kondwani Banda',
            'email' => 'kondwani@thinker.test',
            'qualifications' => 'FinTech & Blockchain Developer',
            'proposal_type' => 'new',
            'proposed_course_name' => 'Decentralized Finance Engineering',
            'status' => 'pending',
        ]);

        Livewire::test(EditInstructorApplication::class, [
            'record' => $application->getKey(),
        ])
            ->assertSuccessful()
            ->assertActionExists('download_pdf');
    }

    public function test_admin_can_download_attached_roadmap_and_curriculum_endpoints(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('instructor-roadmaps/sample_roadmap.pdf', '%PDF-1.4 sample roadmap content');
        Storage::disk('public')->put('instructor-curricula/sample_curriculum.pdf', '%PDF-1.4 sample curriculum syllabus content');

        $admin = User::factory()->create(['role' => 'admin']);
        $application = InstructorApplication::create([
            'name' => 'Lupiya Mwamba',
            'email' => 'lupiya@thinker.test',
            'qualifications' => 'BSc Computer Science',
            'proposal_type' => 'new',
            'proposed_course_name' => 'Cybersecurity Analyst Training',
            'full_roadmap_path' => 'instructor-roadmaps/sample_roadmap.pdf',
            'curriculum_path' => 'instructor-curricula/sample_curriculum.pdf',
            'status' => 'pending',
        ]);

        // Download Roadmap
        $roadmapResponse = $this->actingAs($admin)->get(route('reports.instructor-application.attachment', [
            'application' => $application->id,
            'type' => 'full_roadmap',
        ]));
        $roadmapResponse->assertOk();
        $this->assertStringContainsString('lupiya_mwamba_Full_Curriculum_Roadmap', (string) $roadmapResponse->headers->get('content-disposition'));

        // Download Curriculum
        $curriculumResponse = $this->actingAs($admin)->get(route('reports.instructor-application.attachment', [
            'application' => $application->id,
            'type' => 'curriculum',
        ]));
        $curriculumResponse->assertOk();
        $this->assertStringContainsString('lupiya_mwamba_Curriculum_Syllabus', (string) $curriculumResponse->headers->get('content-disposition'));
    }

    public function test_admin_can_download_all_attachments_zip(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('instructor-roadmaps/devops_roadmap.pdf', 'dummy roadmap pdf bytes');
        Storage::disk('public')->put('instructor-curricula/devops_curriculum.pdf', 'dummy curriculum pdf bytes');

        $admin = User::factory()->create(['role' => 'admin']);
        $application = InstructorApplication::create([
            'name' => 'Bwalya Chanda',
            'email' => 'bwalya@thinker.test',
            'qualifications' => 'DevOps Professional',
            'proposal_type' => 'new',
            'proposed_course_name' => 'Cloud Architecture',
            'full_roadmap_path' => 'instructor-roadmaps/devops_roadmap.pdf',
            'curriculum_path' => 'instructor-curricula/devops_curriculum.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('reports.instructor-application.attachments-zip', [
            'application' => $application->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');
        $this->assertStringContainsString('Instructor_Attachments_bwalya_chanda', (string) $response->headers->get('content-disposition'));
    }

    public function test_filament_table_and_edit_page_expose_attachment_download_actions(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('instructor-roadmaps/test_roadmap.pdf', 'dummy roadmap');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $application = InstructorApplication::create([
            'name' => 'Taonga Phiri',
            'email' => 'taonga@thinker.test',
            'qualifications' => 'AI Engineer',
            'proposal_type' => 'existing',
            'roadmap_path' => 'instructor-roadmaps/test_roadmap.pdf',
            'status' => 'pending',
        ]);

        // Table action
        Livewire::test(ListInstructorApplications::class)
            ->assertSuccessful()
            ->assertTableActionExists('download_attachments');

        // Edit page header action
        Livewire::test(EditInstructorApplication::class, [
            'record' => $application->getKey(),
        ])
            ->assertSuccessful()
            ->assertActionExists('download_attachments_zip');
    }
}

