<?php

namespace App\Filament\Actions;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\ProgressionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class EnrollStudentAction
{
    /**
     * Row action on a Course table (Admin or Instructor) to enroll one or more students into that course.
     */
    public static function forCourse(bool $isInstructor = false): Action
    {
        return Action::make('enrollStudent')
            ->label('Enroll Student')
            ->icon('heroicon-m-user-plus')
            ->color('success')
            ->modalHeading('Enroll Student(s) in Course')
            ->modalDescription(fn (Course $record): string => "Add learners directly to {$record->title}.")
            ->modalWidth('lg')
            ->modalSubmitActionLabel('Enroll')
            ->form([
                Select::make('student_ids')
                    ->label('Student(s)')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->options(function (Course $record): array {
                        $enrolledIds = Enrollment::query()
                            ->where('course_id', $record->id)
                            ->pluck('user_id')
                            ->all();

                        return User::query()
                            ->where('role', 'student')
                            ->where('is_active', true)
                            ->when($enrolledIds !== [], fn ($q) => $q->whereNotIn('id', $enrolledIds))
                            ->orderBy('name')
                            ->get(['id', 'name', 'email'])
                            ->mapWithKeys(fn (User $u): array => [
                                $u->id => $u->name . ($u->email ? " ({$u->email})" : ''),
                            ])
                            ->all();
                    }),

                Select::make('course_intake_id')
                    ->label('Class / Intake (optional)')
                    ->options(fn (Course $record): array => CourseIntake::query()
                        ->where('course_id', $record->id)
                        ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                        ->orderBy('start_date')
                        ->pluck('name', 'id')
                        ->all())
                    ->visible(fn (Course $record): bool => CourseIntake::query()
                        ->where('course_id', $record->id)
                        ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                        ->exists())
                    ->searchable()
                    ->nullable(),

                Select::make('payment_status')
                    ->label('Enrollment / Payment Status')
                    ->options([
                        Enrollment::STATUS_FREE => 'Free / Scholarship / Waived',
                        Enrollment::STATUS_PAID => 'Paid',
                        Enrollment::STATUS_PENDING => 'Pending Payment',
                    ])
                    ->default(Enrollment::STATUS_FREE)
                    ->required()
                    ->live(),

                TextInput::make('fee_amount')
                    ->label('Fee Amount (optional)')
                    ->numeric()
                    ->minValue(0)
                    ->visible(fn (callable $get): bool => $get('payment_status') !== Enrollment::STATUS_FREE)
                    ->nullable(),

                Textarea::make('payment_notes')
                    ->label('Notes (optional)')
                    ->rows(2)
                    ->maxLength(500)
                    ->nullable(),
            ])
            ->action(function (Course $record, array $data) use ($isInstructor): void {
                $actor = auth()->user();
                $studentIds = array_values(array_unique(array_map('intval', (array) ($data['student_ids'] ?? []))));
                $status = (string) ($data['payment_status'] ?? Enrollment::STATUS_FREE);
                $isFree = $status === Enrollment::STATUS_FREE;
                $intakeId = ! empty($data['course_intake_id']) ? (int) $data['course_intake_id'] : null;
                $feeAmount = ! $isFree && isset($data['fee_amount']) && $data['fee_amount'] !== ''
                    ? (float) $data['fee_amount']
                    : 0.0;
                $notes = isset($data['payment_notes']) && trim((string) $data['payment_notes']) !== ''
                    ? trim((string) $data['payment_notes'])
                    : null;

                $enrolledCount = 0;
                $progression = app(ProgressionService::class);

                foreach ($studentIds as $studentId) {
                    $student = User::find($studentId);
                    if (! $student) {
                        continue;
                    }

                    $enrollment = Enrollment::firstOrCreate(
                        [
                            'user_id' => $student->id,
                            'course_id' => $record->id,
                        ],
                        [
                            'tenant_id' => $record->tenant_id ?? $student->tenant_id,
                            'course_intake_id' => $intakeId,
                            'payment_status' => $status,
                            'is_free' => $isFree,
                            'fee_amount' => $feeAmount,
                            'payment_notes' => $notes,
                            'registered_by_user_id' => $actor?->id,
                            'registration_type' => $isInstructor
                                ? Enrollment::CHANNEL_INSTRUCTOR_MANUAL
                                : Enrollment::CHANNEL_ADMIN_MANUAL,
                            'paid_at' => $status === Enrollment::STATUS_PAID ? now() : null,
                        ]
                    );

                    if ($enrollment->wasRecentlyCreated) {
                        $enrolledCount++;
                        if ($record->lessons()->exists()) {
                            $progression->initializeCourseProgress($student, $record);
                        }
                    }
                }

                Notification::make()
                    ->title($enrolledCount > 0 ? 'Student(s) Enrolled' : 'Already Enrolled')
                    ->body($enrolledCount > 0
                        ? "Enrolled {$enrolledCount} student" . ($enrolledCount === 1 ? '' : 's') . " into {$record->title}."
                        : "Selected student(s) were already enrolled in {$record->title}.")
                    ->success()
                    ->send();
            });
    }

    /**
     * Row action on a User/Student table (Admin or Instructor) to enroll that student into a course.
     *
     * @param  (callable(): array<int, int>)|null  $allowedCourseIdsResolver
     */
    public static function forStudent(bool $isInstructor = false, ?callable $allowedCourseIdsResolver = null): Action
    {
        return Action::make('enrollInCourse')
            ->label('Enroll in Course')
            ->icon('heroicon-m-academic-cap')
            ->color('success')
            ->modalHeading('Enroll Student in Course')
            ->modalDescription(fn (User $record): string => "Select a course to enroll {$record->name}.")
            ->modalWidth('lg')
            ->modalSubmitActionLabel('Enroll Student')
            ->visible(fn (User $record): bool => $record->role === 'student')
            ->form([
                Select::make('course_id')
                    ->label('Course')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->options(function (User $record) use ($allowedCourseIdsResolver): array {
                        $alreadyEnrolled = Enrollment::query()
                            ->where('user_id', $record->id)
                            ->pluck('course_id')
                            ->all();

                        $query = Course::query()
                            ->where('is_active', true)
                            ->when($alreadyEnrolled !== [], fn ($q) => $q->whereNotIn('id', $alreadyEnrolled))
                            ->orderBy('title');

                        if ($allowedCourseIdsResolver !== null) {
                            $allowedIds = $allowedCourseIdsResolver();
                            $query->whereIn('id', $allowedIds);
                        }

                        return $query->get(['id', 'title', 'code'])
                            ->mapWithKeys(fn (Course $c): array => [
                                $c->id => $c->title . ($c->code ? " ({$c->code})" : ''),
                            ])
                            ->all();
                    }),

                Select::make('course_intake_id')
                    ->label('Class / Intake (optional)')
                    ->options(function (callable $get): array {
                        $courseId = (int) ($get('course_id') ?? 0);
                        if ($courseId <= 0) {
                            return [];
                        }

                        return CourseIntake::query()
                            ->where('course_id', $courseId)
                            ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                            ->orderBy('start_date')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->visible(function (callable $get): bool {
                        $courseId = (int) ($get('course_id') ?? 0);

                        return $courseId > 0 && CourseIntake::query()
                            ->where('course_id', $courseId)
                            ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                            ->exists();
                    })
                    ->searchable()
                    ->nullable(),

                Select::make('payment_status')
                    ->label('Enrollment / Payment Status')
                    ->options([
                        Enrollment::STATUS_FREE => 'Free / Scholarship / Waived',
                        Enrollment::STATUS_PAID => 'Paid',
                        Enrollment::STATUS_PENDING => 'Pending Payment',
                    ])
                    ->default(Enrollment::STATUS_FREE)
                    ->required()
                    ->live(),

                TextInput::make('fee_amount')
                    ->label('Fee Amount (optional)')
                    ->numeric()
                    ->minValue(0)
                    ->visible(fn (callable $get): bool => $get('payment_status') !== Enrollment::STATUS_FREE)
                    ->nullable(),

                Textarea::make('payment_notes')
                    ->label('Notes (optional)')
                    ->rows(2)
                    ->maxLength(500)
                    ->nullable(),
            ])
            ->action(function (User $record, array $data) use ($isInstructor, $allowedCourseIdsResolver): void {
                $actor = auth()->user();
                $courseId = (int) ($data['course_id'] ?? 0);

                if ($allowedCourseIdsResolver !== null && ! in_array($courseId, $allowedCourseIdsResolver(), true)) {
                    Notification::make()
                        ->title('Unauthorized Course')
                        ->body('You can only enroll students into your assigned courses.')
                        ->danger()
                        ->send();

                    return;
                }

                $course = Course::find($courseId);
                if (! $course) {
                    return;
                }

                $status = (string) ($data['payment_status'] ?? Enrollment::STATUS_FREE);
                $isFree = $status === Enrollment::STATUS_FREE;
                $intakeId = ! empty($data['course_intake_id']) ? (int) $data['course_intake_id'] : null;
                $feeAmount = ! $isFree && isset($data['fee_amount']) && $data['fee_amount'] !== ''
                    ? (float) $data['fee_amount']
                    : 0.0;
                $notes = isset($data['payment_notes']) && trim((string) $data['payment_notes']) !== ''
                    ? trim((string) $data['payment_notes'])
                    : null;

                $enrollment = Enrollment::firstOrCreate(
                    [
                        'user_id' => $record->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'tenant_id' => $course->tenant_id ?? $record->tenant_id,
                        'course_intake_id' => $intakeId,
                        'payment_status' => $status,
                        'is_free' => $isFree,
                        'fee_amount' => $feeAmount,
                        'payment_notes' => $notes,
                        'registered_by_user_id' => $actor?->id,
                        'registration_type' => $isInstructor
                            ? Enrollment::CHANNEL_INSTRUCTOR_MANUAL
                            : Enrollment::CHANNEL_ADMIN_MANUAL,
                        'paid_at' => $status === Enrollment::STATUS_PAID ? now() : null,
                    ]
                );

                if ($enrollment->wasRecentlyCreated && $course->lessons()->exists()) {
                    app(ProgressionService::class)->initializeCourseProgress($record, $course);
                }

                Notification::make()
                    ->title($enrollment->wasRecentlyCreated ? 'Student Enrolled' : 'Already Enrolled')
                    ->body($enrollment->wasRecentlyCreated
                        ? "{$record->name} has been enrolled in {$course->title}."
                        : "{$record->name} is already enrolled in {$course->title}.")
                    ->success()
                    ->send();
            });
    }
}
