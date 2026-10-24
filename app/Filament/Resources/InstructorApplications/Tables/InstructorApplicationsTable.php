<?php

namespace App\Filament\Resources\InstructorApplications\Tables;

use App\Models\InstructorApplication;
use App\Services\InstructorApplicationAttachmentService;
use App\Services\InstructorApplicationPdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class InstructorApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('proposal_type')
                    ->label('Proposal')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'new' => 'info',
                        'existing' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'new' => 'New Course',
                        'existing' => 'Existing Course',
                        default => 'Legacy',
                    })
                    ->sortable(),
                TextColumn::make('preferredCourse.title')
                    ->label('Preferred Course')
                    ->placeholder('Not specified')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('assignedCourse.title')
                    ->label('Assigned Course')
                    ->placeholder('Not assigned')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->searchable(),
                TextColumn::make('prompt_course_creation')
                    ->label('Course Creation')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Prompted with Guide' : '—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not reviewed'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('assign_course')
                    ->label('Assign Course')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->modalHeading('Assign Existing Course')
                    ->modalDescription('Select an existing course to assign to this instructor.')
                    ->form([
                        \Filament\Forms\Components\Select::make('course_id')
                            ->label('Select Existing Course')
                            ->options(fn () => \App\Models\Course::query()->orderBy('title')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->title}" . ($c->code ? " ({$c->code})" : '')]))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn (InstructorApplication $record) => $record->assigned_course_id ?? $record->preferred_course_id),

                        \Filament\Forms\Components\Toggle::make('approve_now')
                            ->label('Approve & Activate Instructor Account Now')
                            ->default(true),
                    ])
                    ->action(function (InstructorApplication $record, array $data) {
                        $courseId = (int) $data['course_id'];
                        $approveNow = (bool) ($data['approve_now'] ?? false);

                        $record->update([
                            'assigned_course_id' => $courseId,
                            'course_assignment_action' => 'assign_existing',
                            'prompt_course_creation' => false,
                            'status' => $approveNow ? 'approved' : $record->status,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $user = $record->user;
                        if ($user && $approveNow) {
                            $user->update([
                                'role' => 'instructor',
                                'is_active' => true,
                                'email_verified_at' => $user->email_verified_at ?? now(),
                            ]);

                            $user->instructorCourses()->syncWithoutDetaching([$courseId]);

                            \App\Models\Course::query()->whereKey($courseId)->update(['is_active' => true]);

                            \Filament\Notifications\Notification::make()
                                ->title('Course Assigned!')
                                ->body('You have been assigned to teach the course.')
                                ->success()
                                ->sendToDatabase($user);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Course Assigned')
                            ->body('The instructor has been assigned to the course' . ($approveNow ? ' and approved.' : '.'))
                            ->success()
                            ->send();
                    }),

                Action::make('prompt_course')
                    ->label('Prompt with Guide')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->modalHeading('Prompt Instructor to Create Course with Guide')
                    ->modalDescription('Invite this instructor to build a new course using Thinker Hub\'s standardized Course Build Structure Guide.')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('course_creation_guidance')
                            ->label('Custom Guidance / Instructions (Optional)')
                            ->placeholder('e.g. Please design a course on Full-Stack Next.js 15 with Server Actions...')
                            ->rows(3),

                        \Filament\Forms\Components\Toggle::make('approve_now')
                            ->label('Approve & Activate Instructor Account Now')
                            ->default(true),

                        \Filament\Forms\Components\ViewField::make('guide_preview')
                            ->label('Course Build Structure Guide Preview')
                            ->view('filament.components.course-creation-guide-modal'),
                    ])
                    ->action(function (InstructorApplication $record, array $data) {
                        $guidance = $data['course_creation_guidance'] ?? null;
                        $approveNow = (bool) ($data['approve_now'] ?? false);

                        $record->update([
                            'course_assignment_action' => 'prompt_create_course',
                            'prompt_course_creation' => true,
                            'course_creation_guidance' => $guidance,
                            'prompted_at' => now(),
                            'status' => $approveNow ? 'approved' : $record->status,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $user = $record->user;
                        if ($user) {
                            if ($approveNow) {
                                $user->update([
                                    'role' => 'instructor',
                                    'is_active' => true,
                                    'email_verified_at' => $user->email_verified_at ?? now(),
                                ]);
                            }

                            \Filament\Notifications\Notification::make()
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

                        \Filament\Notifications\Notification::make()
                            ->title('Instructor Prompted to Create Course')
                            ->body('The instructor has been prompted with the structure guide' . ($approveNow ? ' and activated.' : '.'))
                            ->success()
                            ->send();
                    }),

                Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (InstructorApplication $record) {
                        $service = app(InstructorApplicationPdfService::class);
                        $pdf = $service->renderApplicationPdf($record);
                        $filename = $service->getPdfFilename($record);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                Action::make('download_attachments')
                    ->label('Download Attachments (ZIP)')
                    ->icon('heroicon-o-folder-arrow-down')
                    ->color('success')
                    ->visible(fn (InstructorApplication $record): bool => app(InstructorApplicationAttachmentService::class)->hasAttachments($record))
                    ->action(fn (InstructorApplication $record) => app(InstructorApplicationAttachmentService::class)->downloadApplicationAttachmentsZip($record)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('download_selected_pdf')
                        ->label('Download Selected (PDF)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records) {
                            if ($records->isEmpty()) {
                                return null;
                            }

                            $service = app(InstructorApplicationPdfService::class);

                            if ($records->count() === 1) {
                                /** @var InstructorApplication $first */
                                $first = $records->first();
                                $pdf = $service->renderApplicationPdf($first);
                                $filename = $service->getPdfFilename($first);
                            } else {
                                $pdf = $service->renderCombinedApplicationsPdf($records);
                                $filename = 'Instructor_Applications_Batch_' . date('Ymd_His') . '.pdf';
                            }

                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                $filename,
                                ['Content-Type' => 'application/pdf']
                            );
                        }),
                    BulkAction::make('download_selected_attachments')
                        ->label('Download Selected Attachments (ZIP)')
                        ->icon('heroicon-o-folder-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            return app(InstructorApplicationAttachmentService::class)->downloadMultipleApplicationsAttachmentsZip($records);
                        }),
                ]),
            ]);
    }
}
