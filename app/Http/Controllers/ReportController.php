<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\InstructorApplication;
use App\Models\User;
use App\Services\InstructorApplicationAttachmentService;
use App\Services\InstructorApplicationPdfService;
use App\Services\ReportGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        protected ReportGenerationService $reportService,
        protected InstructorApplicationPdfService $applicationPdfService,
        protected InstructorApplicationAttachmentService $attachmentService
    ) {}

    /**
     * Ensure authorized administrators can access sensitive admin dossiers.
     */
    protected function authorizeAdminAccess(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->role === 'admin' || (bool) $user->canSwitchToAdmin()), 403, 'Unauthorized access: Administrator privileges required.');
    }

    /**
     * Ensure authorized administrators and instructors can access the reporting engine.
     */
    protected function authorizeReportAccess(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->role === 'admin' || $user->role === 'instructor' || (bool) $user->canSwitchToAdmin() || (bool) $user->canSwitchToInstructor()), 403, 'Unauthorized access to the Thinker HUB Reporting Engine.');
    }

    /**
     * Download or preview Student Academic Dossier & Answer Sheets PDF.
     */
    public function studentReport(Request $request, User $student): Response
    {
        $this->authorizeReportAccess($request);

        $courseId = $request->query('course_id');
        $course = $courseId ? Course::find($courseId) : null;

        $options = [
            'include_answer_sheets' => $request->boolean('include_answer_sheets', true),
            'include_attendance_log' => $request->boolean('include_attendance_log', true),
        ];

        try {
            $pdf = $this->reportService->renderStudentReportPdf($student, $course, $options);
            
            $studentSlug = Str::slug($student->name, '_');
            $courseSlug = $course ? '_' . Str::slug($course->code ?? $course->title, '_') : '';
            $filename = "ThinkerHUB_Student_Report_{$studentSlug}{$courseSlug}_" . date('Ymd') . ".pdf";

            if ($request->boolean('stream')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Unable to generate student academic report: ' . $e->getMessage());
        }
    }

    /**
     * Download or preview Course Executive Analytics PDF.
     */
    public function courseReport(Request $request, Course $course): Response
    {
        $this->authorizeReportAccess($request);

        $intakeId = $request->query('intake_id');
        $intakeId = $intakeId ? (int) $intakeId : null;

        try {
            $pdf = $this->reportService->renderCourseReportPdf($course, $intakeId);

            $courseSlug = Str::slug($course->code ?? $course->title, '_');
            $intakeSlug = $intakeId ? '_Intake_' . $intakeId : '';
            $filename = "ThinkerHUB_Course_Analytics_{$courseSlug}{$intakeSlug}_" . date('Ymd') . ".pdf";

            if ($request->boolean('stream')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Unable to generate course analytics report: ' . $e->getMessage());
        }
    }

    /**
     * Download or preview Cohort / Intake Performance PDF.
     */
    public function intakeReport(Request $request, CourseIntake $intake): Response
    {
        $this->authorizeReportAccess($request);

        try {
            $pdf = $this->reportService->renderIntakeReportPdf($intake);

            $intakeSlug = Str::slug($intake->name, '_');
            $filename = "ThinkerHUB_Cohort_Report_{$intakeSlug}_" . date('Ymd') . ".pdf";

            if ($request->boolean('stream')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Unable to generate cohort performance report: ' . $e->getMessage());
        }
    }

    /**
     * Download or preview Instructor Application Dossier PDF.
     */
    public function instructorApplicationReport(Request $request, InstructorApplication $application): Response
    {
        $this->authorizeAdminAccess($request);

        try {
            $pdf = $this->applicationPdfService->renderApplicationPdf($application);
            $filename = $this->applicationPdfService->getPdfFilename($application);

            if ($request->boolean('stream')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Unable to generate instructor application dossier: ' . $e->getMessage());
        }
    }

    /**
     * Download or view a specific attached document (roadmap, full_roadmap, curriculum, cv).
     */
    public function instructorApplicationAttachment(Request $request, InstructorApplication $application, string $type): Response
    {
        $this->authorizeAdminAccess($request);

        $response = $request->boolean('view') || $request->boolean('stream')
            ? $this->attachmentService->viewSingleAttachment($application, $type)
            : $this->attachmentService->downloadSingleAttachment($application, $type);

        if (! $response) {
            abort(404, 'Requested attachment document was not found.');
        }

        return $response;
    }

    /**
     * Download all attached documents for an application bundled in a ZIP archive.
     */
    public function instructorApplicationAttachmentsZip(Request $request, InstructorApplication $application): Response
    {
        $this->authorizeAdminAccess($request);

        $download = $this->attachmentService->downloadApplicationAttachmentsZip($application);
        if (! $download) {
            abort(404, 'No attached documents available for this application.');
        }

        return $download;
    }
}


