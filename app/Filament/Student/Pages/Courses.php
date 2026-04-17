<?php

namespace App\Filament\Student\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class Courses extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.student.pages.courses';

    public array $courses = [];

    public int $enrolledCount = 0;

    public function mount(): void
    {
        $this->refreshCourses();
    }

    public function enroll(int $courseId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $alreadyEnrolled = $user->courses()->where('courses.id', $courseId)->exists();

        if ($alreadyEnrolled) {
            Notification::make()
                ->title('You are already enrolled in this course.')
                ->warning()
                ->send();

            return;
        }

        $currentEnrollments = (int) $user->courses()->count();

        if ($currentEnrollments >= 2) {
            Notification::make()
                ->title('Enrollment limit reached (max 2 courses).')
                ->danger()
                ->send();

            return;
        }

        $course = Course::query()->whereKey($courseId)->where('is_active', true)->first();

        if (! $course) {
            Notification::make()
                ->title('Selected course is not available.')
                ->danger()
                ->send();

            return;
        }

        Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        Notification::make()
            ->title('Course enrolled successfully.')
            ->success()
            ->send();

        $this->refreshCourses();
    }

    public function unenroll(int $courseId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->delete();

        Notification::make()
            ->title('Enrollment removed.')
            ->success()
            ->send();

        $this->refreshCourses();
    }

    protected function refreshCourses(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();
        $this->enrolledCount = count($enrolledCourseIds);

        $this->courses = Course::query()
            ->with('instructors')
            ->orderBy('title')
            ->get()
            ->map(fn (Course $course): array => [
                'id' => $course->id,
                'title' => $course->title,
                'code' => $course->code,
                'summary' => Str::limit($course->description ?: 'No summary available.', 90),
                'description' => $course->description ?: 'No full description available.',
                'is_active' => $course->is_active,
                'enrolled' => in_array($course->id, $enrolledCourseIds, true),
                'instructors' => $course->instructors->map(fn ($instructor): array => [
                    'name' => $instructor->name,
                    'proficiency' => $instructor->proficiency,
                    'occupation' => $instructor->occupation,
                    'whatsapp' => $instructor->whatsapp,
                    'linkedin_url' => $instructor->linkedin_url,
                    'facebook_url' => $instructor->facebook_url,
                    'photo' => $instructor->profile_photo_path,
                ])->all(),
            ])
            ->all();
    }
}
