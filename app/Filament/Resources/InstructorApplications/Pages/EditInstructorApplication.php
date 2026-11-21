<?php

namespace App\Filament\Resources\InstructorApplications\Pages;

use App\Filament\Resources\InstructorApplications\InstructorApplicationResource;
use App\Models\Course;
use App\Models\InstructorApplication;
use App\Services\InstructorApplicationAttachmentService;
use App\Services\InstructorApplicationPdfService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Filament\Resources\Pages\BaseEditRecord;
use Illuminate\Support\Str;

class EditInstructorApplication extends BaseEditRecord
{
    protected static string $resource = InstructorApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label('Download Application PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    /** @var InstructorApplication $record */
                    $record = $this->record;
                    $service = app(InstructorApplicationPdfService::class);
                    $pdf = $service->renderApplicationPdf($record);
                    $filename = $service->getPdfFilename($record);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $filename,
                        ['Content-Type' => 'application/pdf']
                    );
                }),

            Action::make('download_attachments_zip')
                ->label('Download All Attachments (ZIP)')
                ->icon('heroicon-o-folder-arrow-down')
                ->color('success')
                ->visible(function () {
                    /** @var InstructorApplication $record */
                    $record = $this->record;
                    return app(InstructorApplicationAttachmentService::class)->hasAttachments($record);
                })
                ->action(function () {
                    /** @var InstructorApplication $record */
                    $record = $this->record;
                    return app(InstructorApplicationAttachmentService::class)->downloadApplicationAttachmentsZip($record);
                }),

            Action::make('assign_existing_course')
                ->label('Assign Existing Course')
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->modalHeading('Assign Instructor to Existing Course')
                ->modalDescription('Select an existing course to assign to this instructor. If approved, the course will be linked to the instructor and activated.')
                ->form([
                    \Filament\Forms\Components\Select::make('course_id')
                        ->label('Select Existing Course')
                        ->options(fn () => Course::query()->orderBy('title')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->title}" . ($c->code ? " ({$c->code})" : '')]))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => $this->record->assigned_course_id ?? $this->record->preferred_course_id),

                    \Filament\Forms\Components\Toggle::make('approve_now')
                        ->label('Approve & Activate Instructor Account Now')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    /** @var InstructorApplication $application */
                    $application = $this->record;
                    $courseId = (int) $data['course_id'];
                    $approveNow = (bool) ($data['approve_now'] ?? false);

                    $application->update([
                        'assigned_course_id' => $courseId,
                        'course_assignment_action' => 'assign_existing',
                        'prompt_course_creation' => false,
                        'status' => $approveNow ? 'approved' : $application->status,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    $user = $application->user;
                    if ($user && $approveNow) {
                        $user->update([
                            'role' => 'instructor',
                            'is_active' => true,
                            'email_verified_at' => $user->email_verified_at ?? now(),
                            'bio' => $application->bio ?: $user->bio,
                            'proficiency' => $application->proficiency ?: $user->proficiency,
                            'occupation' => $application->occupation ?: $user->occupation,
                        ]);

                        $user->instructorCourses()->syncWithoutDetaching([$courseId]);

                        Course::query()->whereKey($courseId)->update(['is_active' => true]);

                        Notification::make()
                            ->title('Course Assigned!')
                            ->body("You have been assigned to teach the course.")
                            ->success()
                            ->sendToDatabase($user);
                    }

                    Notification::make()
                        ->title('Course Assigned')
                        ->body("The instructor has been assigned to the selected course" . ($approveNow ? " and approved." : "."))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'assigned_course_id', 'course_assignment_action']);
                }),

            Action::make('unassign_course')
                ->label('Unassign Course')
                ->icon('heroicon-o-user-minus')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Unassign Course from Instructor')
                ->modalDescription(function () {
                    /** @var InstructorApplication $record */
                    $record = $this->record;
                    $courseTitle = $record->assignedCourse?->title ?? $record->preferredCourse?->title ?? 'the currently assigned course';
                    return "Are you sure you want to un-assign {$courseTitle} from this instructor? They will no longer be assigned to teach this course.";
                })
                ->visible(function () {
                    /** @var InstructorApplication $record */
                    $record = $this->record;
                    return filled($record->assigned_course_id)
                        || ($record->user && $record->user->instructorCourses()->exists());
                })
                ->action(function () {
                    /** @var InstructorApplication $application */
                    $application = $this->record;
                    $user = $application->user;
                    $courseId = $application->assigned_course_id ?: ($application->preferred_course_id ? (int) $application->preferred_course_id : null);

                    if ($user) {
                        if ($courseId) {
                            $user->instructorCourses()->detach($courseId);
                        } else {
                            $user->instructorCourses()->detach();
                        }
                    }

                    $application->update([
                        'assigned_course_id' => null,
                        'course_assignment_action' => 'unassign',
                        'prompt_course_creation' => false,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Course Unassigned')
                        ->body('The course has been successfully unassigned from this instructor.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['assigned_course_id', 'course_assignment_action', 'prompt_course_creation']);
                }),

            Action::make('prompt_create_course')
                ->label('Prompt to Create Course with Guide')
                ->icon('heroicon-o-document-plus')
                ->color('warning')
                ->modalHeading('Prompt Instructor to Create Course with Guide')
                ->modalDescription('Invite this instructor to draft and structure a new course following Thinker Hub\'s standardized Course Build Structure Guide.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('course_creation_guidance')
                        ->label('Custom Guidance / Instructions (Optional)')
                        ->placeholder('e.g. Please design a course on Full-Stack Next.js 15 with Server Actions...')
                        ->rows(3),

                    \Filament\Forms\Components\Toggle::make('approve_now')
                        ->label('Approve & Activate Instructor Account Now')
                        ->helperText('Activating the instructor account allows them to log in to their dashboard and access the course creation studio.')
                        ->default(true),

                    \Filament\Forms\Components\ViewField::make('guide_preview')
                        ->label('Course Build Structure Guide Preview')
                        ->view('filament.components.course-creation-guide-modal'),
                ])
                ->action(function (array $data) {
                    /** @var InstructorApplication $application */
                    $application = $this->record;
                    $guidance = $data['course_creation_guidance'] ?? null;
                    $approveNow = (bool) ($data['approve_now'] ?? false);

                    $application->update([
                        'course_assignment_action' => 'prompt_create_course',
                        'prompt_course_creation' => true,
                        'course_creation_guidance' => $guidance,
                        'prompted_at' => now(),
                        'status' => $approveNow ? 'approved' : $application->status,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    $user = $application->user;
                    if ($user) {
                        if ($approveNow) {
                            $user->update([
                                'role' => 'instructor',
                                'is_active' => true,
                                'email_verified_at' => $user->email_verified_at ?? now(),
                                'bio' => $application->bio ?: $user->bio,
                                'proficiency' => $application->proficiency ?: $user->proficiency,
                                'occupation' => $application->occupation ?: $user->occupation,
                            ]);
                        }

                        Notification::make()
                            ->title('Course Creation Request from Admin')
                            ->body('You have been invited by the administration to build your new course. Use our Course Build Structure Guide to draft and submit your curriculum.')
                            ->icon('heroicon-o-academic-cap')
                            ->actions([
                                Action::make('create_course')
                                    ->button()
                                    ->label('Create Course Now')
                                    ->url(\App\Filament\Instructor\Resources\CourseResource\CourseResource::getUrl('create', panel: 'instructor')),
                            ])
                            ->success()
                            ->sendToDatabase($user);
                    }

                    Notification::make()
                        ->title('Instructor Prompted to Create Course')
                        ->body("The instructor has been prompted to create a course with the structure guide" . ($approveNow ? " and their account has been activated." : "."))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'course_assignment_action', 'course_creation_guidance']);
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var InstructorApplication $application */
        $application = $this->record;

        if ($application->status === 'approved') {
            $user = $application->user;

            if ($user) {
                $user->update([
                    'role' => 'instructor',
                    'is_active' => true,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'bio' => $application->bio ?: $user->bio,
                    'proficiency' => $application->proficiency ?: $user->proficiency,
                    'occupation' => $application->occupation ?: $user->occupation,
                    'whatsapp' => $application->whatsapp ?: $user->whatsapp,
                    'linkedin_url' => $application->linkedin_url ?: $user->linkedin_url,
                    'facebook_url' => $application->facebook_url ?: $user->facebook_url,
                ]);

                // Determine course assignment based on admin action or fallback
                $action = $application->course_assignment_action;
                $assignedCourseId = $application->assigned_course_id ?: ($application->proposal_type === 'existing' ? $application->preferred_course_id : null);

                if ($action === 'unassign') {
                    if ($application->assigned_course_id) {
                        $user->instructorCourses()->detach($application->assigned_course_id);
                    }
                    if ($application->preferred_course_id) {
                        $user->instructorCourses()->detach((int) $application->preferred_course_id);
                    }
                    $application->updateQuietly([
                        'assigned_course_id' => null,
                        'prompt_course_creation' => false,
                    ]);
                } elseif ($action === 'assign_existing' || (! $action && $assignedCourseId)) {
                    if ($assignedCourseId) {
                        $user->instructorCourses()->syncWithoutDetaching([$assignedCourseId]);

                        Course::query()
                            ->whereKey($assignedCourseId)
                            ->update(['is_active' => true]);

                        $application->update(['prompt_course_creation' => false]);
                    }
                } elseif ($action === 'prompt_create_course' || $application->prompt_course_creation) {
                    $application->update([
                        'prompt_course_creation' => true,
                        'prompted_at' => $application->prompted_at ?? now(),
                    ]);

                    Notification::make()
                        ->title('Course Creation Request from Admin')
                        ->body('You have been invited by the administration to build your new course. Use our Course Build Structure Guide to draft and submit your curriculum.')
                        ->icon('heroicon-o-academic-cap')
                        ->actions([
                            Action::make('create_course')
                                ->button()
                                ->label('Create Course Now')
                                ->url(\App\Filament\Instructor\Resources\CourseResource\CourseResource::getUrl('create', panel: 'instructor')),
                        ])
                        ->success()
                        ->sendToDatabase($user);
                } elseif ($action === 'keep_proposal' || (! $action && $application->proposal_type === 'new')) {
                    if (filled($application->proposed_course_name) && filled($application->proposed_course_code)) {
                        $course = Course::query()
                            ->where('title', $application->proposed_course_name)
                            ->where('code', $application->proposed_course_code)
                            ->first();

                        if (! $course) {
                            $course = Course::query()->create([
                                'title' => (string) $application->proposed_course_name,
                                'code' => (string) $application->proposed_course_code,
                                'description' => $application->proposed_course_description,
                                'overview' => $application->proposed_course_overview,
                                'timeline' => $application->proposed_course_timeline,
                                'fees' => self::normalizeFeeProposal((string) ($application->proposed_course_fees ?? '')),
                                'requirements' => self::normalizeLines((string) ($application->proposed_course_requirements ?? '')),
                                'level_progression' => self::normalizeLevelProgressionProposal((string) ($application->proposed_course_level_progression ?? '')),
                                'key_outcome' => $application->proposed_course_key_outcome,
                                'is_open_enrollment' => (bool) $application->proposed_course_is_open_enrollment,
                                'is_active' => true,
                            ]);
                        } else {
                            $course->update([
                                'is_active' => true,
                            ]);
                        }

                        $user->instructorCourses()->syncWithoutDetaching([$course->id]);
                    }
                }
            }

            $application->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            Notification::make()
                ->title('Instructor approved')
                ->body("Instructor account for {$application->name} has been activated.")
                ->success()
                ->send();
        } elseif ($application->status === 'rejected') {
            $application->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Deactivate the user if they have no other approved applications
            $user = $application->user;
            if ($user) {
                $hasOtherApproved = InstructorApplication::query()
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $application->id)
                    ->where('status', 'approved')
                    ->exists();

                if (! $hasOtherApproved) {
                    $user->update(['is_active' => false]);
                }
            }

            Notification::make()
                ->title('Application rejected')
                ->body("Application from {$application->name} has been rejected.")
                ->warning()
                ->send();
        }
    }

    private static function normalizeLines(string $input): ?string
    {
        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', $input) ?: []
        )));

        if ($lines === []) {
            return null;
        }

        return implode(PHP_EOL, $lines);
    }

    private static function normalizeFeeProposal(string $input): ?string
    {
        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', $input) ?: []
        )));

        if ($lines === []) {
            return null;
        }

        $entries = [];

        foreach ($lines as $line) {
            $parts = array_values(array_filter(array_map('trim', explode('+', $line))));

            if (count($parts) < 3) {
                continue;
            }

            $categoryRaw = Str::lower($parts[0]);
            $catKey = 'one_on_one';
            if (Str::contains($categoryRaw, 'self') || Str::contains($categoryRaw, 'paced')) {
                $catKey = 'self_paced';
            } elseif (Str::contains($categoryRaw, 'group')) {
                $catKey = 'group';
            }

            $entries[] = [
                'category' => $catKey,
                'level' => self::normalizeLevelLabel($parts[1]),
                'amount' => $parts[2],
                'duration' => $parts[3] ?? '',
            ];
        }

        if ($entries === []) {
            return null;
        }

        $grouped = [
            'one_on_one' => [],
            'group' => [],
            'self_paced' => [],
        ];

        foreach ($entries as $entry) {
            $grouped[$entry['category']][] = [
                'level' => $entry['level'],
                'amount' => $entry['amount'],
                'duration' => $entry['duration'],
            ];
        }

        return json_encode($grouped, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: null;
    }

    private static function normalizeLevelProgressionProposal(string $input): ?string
    {
        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', $input) ?: []
        )));

        if ($lines === []) {
            return null;
        }

        $entries = [];

        foreach ($lines as $line) {
            $parts = array_values(array_filter(array_map('trim', explode('+', $line))));

            if (count($parts) < 2) {
                continue;
            }

            $entries[] = [
                'level' => self::normalizeLevelLabel($parts[0]),
                'details' => $parts[1],
            ];
        }

        if ($entries === []) {
            return null;
        }

        return json_encode($entries, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: null;
    }

    private static function normalizeLevelLabel(string $value): string
    {
        return match (Str::lower(trim($value))) {
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            default => trim($value),
        };
    }
}
