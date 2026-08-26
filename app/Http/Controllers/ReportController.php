<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\User;
use App\Services\ReportGenerationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function __construct(
        protected ReportGenerationService $reportService
    ) {}

    /**
     * Ensure only administrators can access the reporting engine.
     */
    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->role === 'admin' || (bool) $user->canSwitchToAdmin()), 403, 'Unauthorized access to the Thinker HUB Reporting Engine.');
    }

    /**
     * Download or preview Student Academic Dossier & Answer Sheets PDF.
     */
    public function studentReport(Request $request, User $student): Response
    {
        $this->authorizeAdmin($request);

        $courseId = $request->query('course_id');
        $course = $courseId ? Course::find($courseId) : null;

        $options = [
            'include_answer_sheets' => $request->boolean('include_answer_sheets', true),
            'include_attendance_log' => $request->boolean('include_attendance_log', true),
        ];

        $pdf = $this->reportService->renderStudentReportPdf($student, $course, $options);
        
        $studentSlug = Str::slug($student->name, '_');
        $courseSlug = $course ? '_' . Str::slug($course->code ?? $course->title, '_') : '';
        $filename = "ThinkerHUB_Student_Report_{$studentSlug}{$courseSlug}_" . date('Ymd') . ".pdf";

        if ($request->boolean('stream')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Download or preview Course Executive Analytics PDF.
     */
    public function courseReport(Request $request, Course $course): Response
    {
        $this->authorizeAdmin($request);

        $intakeId = $request->query('intake_id');
        $intakeId = $intakeId ? (int) $intakeId : null;

        $pdf = $this->reportService->renderCourseReportPdf($course, $intakeId);

        $courseSlug = Str::slug($course->code ?? $course->title, '_');
        $intakeSlug = $intakeId ? '_Intake_' . $intakeId : '';
        $filename = "ThinkerHUB_Course_Analytics_{$courseSlug}{$intakeSlug}_" . date('Ymd') . ".pdf";

        if ($request->boolean('stream')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Download or preview Cohort / Intake Performance PDF.
     */
    public function intakeReport(Request $request, CourseIntake $intake): Response
    {
        $this->authorizeAdmin($request);

        $pdf = $this->reportService->renderIntakeReportPdf($intake);

        $intakeSlug = Str::slug($intake->name, '_');
        $filename = "ThinkerHUB_Cohort_Report_{$intakeSlug}_" . date('Ymd') . ".pdf";

        if ($request->boolean('stream')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
