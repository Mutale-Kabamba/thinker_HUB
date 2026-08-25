<?php

namespace App\Filament\Student\Pages;

use App\Models\Course;
use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Courses extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'LEARNING';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.student.pages.courses';

    public array $courses = [];

    public int $enrolledCount = 0;

    /**
     * @var array<int, int>
     */
    public array $ratingInputs = [];

    /**
     * @var array<int, string>
     */
    public array $reviewInputs = [];

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

        if ($course->is_open_enrollment === false) {
            $isSelectedParticipant = $course->selectedParticipants()
                ->where('users.id', $user->id)
                ->exists();

            if (! $isSelectedParticipant) {
                Notification::make()
                    ->title('This course is locked to selected participants only.')
                    ->warning()
                    ->send();

                return;
            }
        }

        if ($course->isPayable($user->track)) {
            $this->redirect(route('checkout.show', [$course->id, 'track' => $user->track ?: 'Beginner']));

            return;
        }

        $activeIntakeId = $course->activeIntake?->id;

        Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ], [
            'course_intake_id' => $activeIntakeId,
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

    public function setRating(int $courseId, int $rating): void
    {
        if ($rating < 1 || $rating > 5) {
            return;
        }

        $user = auth()->user();

        if (! $user) {
            return;
        }

        $isEnrolled = $user->courses()->where('courses.id', $courseId)->exists();

        if (! $isEnrolled) {
            Notification::make()
                ->title('You must be enrolled to rate this course.')
                ->warning()
                ->send();

            return;
        }

        $this->ratingInputs[$courseId] = $rating;
    }

    public function saveRating(int $courseId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $isEnrolled = $user->courses()->where('courses.id', $courseId)->exists();

        if (! $isEnrolled) {
            Notification::make()
                ->title('You must be enrolled to rate this course.')
                ->warning()
                ->send();

            return;
        }

        $course = Course::query()->whereKey($courseId)->first();

        if (! $course) {
            Notification::make()
                ->title('Course not found.')
                ->danger()
                ->send();

            return;
        }

        $rating = (int) ($this->ratingInputs[$courseId] ?? 0);

        if ($rating < 1 || $rating > 5) {
            Notification::make()
                ->title('Choose a star rating from 1 to 5.')
                ->warning()
                ->send();

            return;
        }

        $review = trim((string) ($this->reviewInputs[$courseId] ?? ''));

        if (mb_strlen($review) > 1000) {
            Notification::make()
                ->title('Review must be 1000 characters or fewer.')
                ->warning()
                ->send();

            return;
        }

        CourseRating::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $rating,
                'review' => $review !== '' ? $review : null,
            ],
        );

        \App\Models\Review::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'reviewable_type' => Course::class,
                'reviewable_id' => $course->id,
            ],
            [
                'rating' => $rating,
                'title' => null,
                'comment' => $review !== '' ? $review : 'Course rating',
                'is_verified' => true,
                'is_approved' => true,
            ],
        );

        Notification::make()
            ->title('Your review has been saved.')
            ->success()
            ->send();

        $this->refreshCourses();
    }

    public function claimCertificate(int $courseId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $course = Course::query()->find($courseId);

        if (! $course) {
            return;
        }

        $certificate = app(CertificateService::class)->issue($user, $course);

        if (! $certificate) {
            $reasons = app(CertificateService::class)->eligibility($user, $course)['reasons'];

            Notification::make()
                ->title('Certificate not available yet')
                ->body($reasons === []
                    ? 'Complete this course to earn its certificate.'
                    : implode(' ', array_map(
                        fn (string $reason): string => rtrim($reason, '.').'.',
                        $reasons,
                    )))
                ->warning()
                ->send();

            return;
        }

        if ($certificate->wasRecentlyCreated) {
            try {
                $user->notify(new CertificateIssuedNotification($certificate));
            } catch (\Throwable $e) {
                report($e);
            }

            Notification::make()
                ->title('Certificate issued!')
                ->body('Your certificate for '.$course->title.' is ready.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Certificate already claimed')
                ->info()
                ->send();
        }

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

        $completedCourseIds = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('course_id')
            ->all();

        $certifiedCourseIds = $user->certificates()
            ->whereIn('course_id', $completedCourseIds)
            ->pluck('course_id')
            ->all();

        $myEnrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->with('intake')
            ->get()
            ->keyBy('course_id');

        $this->courses = Course::query()
            ->with([
                'selectedParticipants:id',
                'intakes' => fn ($q) => $q->orderBy('start_date'),
                'ratings' => fn ($query) => $query
                    ->with('user:id,name')
                    ->latest(),
            ])
            ->orderBy('title')
            ->get()
            ->map(function (Course $course) use ($enrolledCourseIds, $certifiedCourseIds, $myEnrollments, $user): array {
                $selectedParticipantIds = $course->selectedParticipants
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id)
                    ->all();

                $ratings = $course->ratings->values();
                $ratingsCount = $ratings->count();
                $avgRating = $ratingsCount > 0
                    ? round((float) $ratings->avg('rating'), 1)
                    : 0.0;

                $myRating = $ratings->firstWhere('user_id', (int) $user->id);

                $this->ratingInputs[$course->id] = (int) ($myRating?->rating ?? 0);
                $this->reviewInputs[$course->id] = (string) ($myRating?->review ?? '');

                $isOpenEnrollment = $course->is_open_enrollment !== false;
                $isEnrolled = in_array($course->id, $enrolledCourseIds, true);
                $enrollment = $myEnrollments->get($course->id);

                $eligibility = $isEnrolled
                    ? app(CertificateService::class)->eligibility($user, $course)
                    : null;

                $activeIntake = $course->activeIntake;

                $canEnroll = $course->is_active && (
                    $isOpenEnrollment || in_array((int) $user->id, $selectedParticipantIds, true)
                );

                $isPayable = $course->isPayable($user->track);

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'code' => $course->code,
                    'offering_mode' => $course->offering_mode ?? 'once_off',
                    'is_ongoing' => $course->isOngoing(),
                    'intake_name' => $enrollment?->intake?->name ?? ($activeIntake?->name ?? null),
                    'active_intake_dates' => $activeIntake?->formattedDateRange(),
                    'next_intake_date' => $activeIntake?->formattedNextIntake(),
                    'summary' => Str::limit($course->description ?: 'No summary available.', 90),
                    'description' => $course->description ?: 'No full description available.',
                    'is_active' => $course->is_active,
                    'is_open_enrollment' => $isOpenEnrollment,
                    'is_payable' => $isPayable,
                    'fee_amount' => $course->getNumericFeeForLevel($user->track),
                    'checkout_url' => route('checkout.show', [$course->id, 'track' => $user->track ?: 'Beginner']),
                    'enrolled' => $isEnrolled,
                    'certificate_eligible' => $eligibility['eligible'] ?? false,
                    'certificate_lock_reason' => ($eligibility && ! $eligibility['eligible'])
                        ? implode(' and ', $eligibility['reasons'])
                        : null,
                    'certificate_claimed' => in_array($course->id, $certifiedCourseIds, true),
                    'can_enroll' => $canEnroll,
                    'avg_rating' => $avgRating,
                    'ratings_count' => $ratingsCount,
                    'reviews' => $ratings
                        ->take(5)
                        ->map(fn (CourseRating $rating): array => [
                            'id' => $rating->id,
                            'user_name' => $rating->user?->name ?? 'Student',
                            'rating' => (int) $rating->rating,
                            'review' => (string) ($rating->review ?? ''),
                            'created_at' => $rating->created_at?->diffForHumans() ?? 'Recently',
                        ])
                        ->all(),
                ];
            })
            ->all();
    }
}
