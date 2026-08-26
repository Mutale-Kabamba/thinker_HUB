<?php

namespace App\Filament\Instructor\Resources\ReportResource\Pages;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\ReportResource\ReportResource;
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
    use ScopedToInstructor;

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
        $firstCourse = $this->instructorCourses()->first();
        if ($firstCourse) {
            $this->selectedCourseId = $firstCourse->id;
            $firstStudentId = Enrollment::query()->where('course_id', $firstCourse->id)->value('user_id');
            if ($firstStudentId) {
                $this->selectedStudentId = $firstStudentId;
            }
        }
    }

    public function getStudentsProperty(): Collection
    {
        $courseIds = $this->instructorCourseIds();

        return User::query()
            ->whereIn('role', ['student', 'researcher'])
            ->whereHas('enrollments', fn (Builder $q) => $q->whereIn('course_id', $courseIds))
            ->when($this->search, function (Builder $q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->trackFilter, fn ($q) => $q->where('track', $this->trackFilter))
            ->with(['enrollments' => fn ($q) => $q->whereIn('course_id', $courseIds)->with('course')])
            ->orderBy('name')
            ->get();
    }

    public function getCoursesProperty(): Collection
    {
        return Course::query()
            ->whereIn('id', $this->instructorCourseIds())
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

        $targetIntakeId = $intakeId !== null ? $intakeId : $this->selectedIntakeId;
        $service = app(ReportGenerationService::class);
        $pdf = $service->renderCourseReportPdf($course, $targetIntakeId);

        $filename = 'Course_Analytics_' . ($course->code ?: 'Course') . '_' . date('Ymd') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
