<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\ReportGenerationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class GenerateReports extends Page
{
    protected static string $resource = ReportResource::class;

    protected string $view = 'filament.admin.pages.generate-reports';

    protected static ?string $title = 'Academic & Analytics Reports Generator';

    public string $activeTab = 'student'; // 'student', 'course', 'student_directory', 'course_directory'

    public ?int $selectedStudentId = null;

    public ?int $selectedCourseId = null;

    public ?int $selectedIntakeId = null;

    public bool $includeAnswerSheets = true;

    public bool $includeAttendanceLog = true;

    public string $search = '';

    public string $trackFilter = '';

    public function mount(): void
    {
        $firstStudent = User::query()->whereIn('role', ['student', 'researcher'])->orderBy('name')->first();
        if ($firstStudent) {
            $this->selectedStudentId = $firstStudent->id;
        }

        $firstCourse = Course::query()->where('is_active', true)->first();
        if ($firstCourse) {
            $this->selectedCourseId = $firstCourse->id;
        }
    }

    public function getStudentsProperty(): Collection
    {
        return User::query()
            ->whereIn('role', ['student', 'researcher'])
            ->when($this->search, function (Builder $q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->trackFilter, fn ($q) => $q->where('track', $this->trackFilter))
            ->with(['enrollments.course'])
            ->orderBy('name')
            ->get();
    }

    public function getCoursesProperty(): Collection
    {
        return Course::query()
            ->with(['instructors', 'intakes', 'enrollments'])
            ->when($this->search, function (Builder $q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->orderBy('title')
            ->get();
    }

    public function getIntakesProperty(): Collection
    {
        if (! $this->selectedCourseId) {
            return collect();
        }

        return CourseIntake::query()
            ->where('course_id', $this->selectedCourseId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function downloadStudentPdf(?int $studentId = null, ?int $courseId = null): ?Response
    {
        $targetStudentId = $studentId ?: $this->selectedStudentId;
        if (! $targetStudentId) {
            Notification::make()
                ->title('Please select a student.')
                ->warning()
                ->send();

            return null;
        }

        $student = User::find($targetStudentId);
        if (! $student) {
            Notification::make()
                ->title('Student not found.')
                ->danger()
                ->send();

            return null;
        }

        $targetCourseId = $courseId !== null ? $courseId : $this->selectedCourseId;
        $course = $targetCourseId ? Course::find($targetCourseId) : null;

        try {
            $service = app(ReportGenerationService::class);
            $options = [
                'include_answer_sheets' => $this->includeAnswerSheets,
                'include_attendance_log' => $this->includeAttendanceLog,
            ];

            $pdf = $service->renderStudentReportPdf($student, $course, $options);
            $filename = 'Student_Report_' . str_replace(' ', '_', $student->name) . '_' . date('Ymd') . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Failed to generate Student Report')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }

    public function downloadCoursePdf(?int $courseId = null, ?int $intakeId = null): ?Response
    {
        $targetCourseId = $courseId ?: $this->selectedCourseId;
        if (! $targetCourseId) {
            Notification::make()
                ->title('Please select a course.')
                ->warning()
                ->send();

            return null;
        }

        $course = Course::find($targetCourseId);
        if (! $course) {
            Notification::make()
                ->title('Course not found.')
                ->danger()
                ->send();

            return null;
        }

        try {
            $targetIntakeId = $intakeId !== null ? $intakeId : $this->selectedIntakeId;
            $service = app(ReportGenerationService::class);
            $pdf = $service->renderCourseReportPdf($course, $targetIntakeId);

            $filename = 'Course_Analytics_' . ($course->code ?: 'Course') . '_' . date('Ymd') . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Failed to generate Course Analytics Report')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }
}
