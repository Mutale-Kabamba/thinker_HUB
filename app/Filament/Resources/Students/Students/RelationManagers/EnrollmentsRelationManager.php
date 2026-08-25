<?php

namespace App\Filament\Resources\Students\Students\RelationManagers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use App\Services\GamificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Course Enrollments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('course_id')
                ->label('Course')
                ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('course_intake_id', null)),

            Select::make('course_intake_id')
                ->label('Class / Intake (Cohort)')
                ->placeholder('Select intake cohort (Optional)')
                ->options(function (Get $get): array {
                    $courseId = $get('course_id');
                    if (! $courseId) {
                        return [];
                    }

                    return CourseIntake::query()
                        ->where('course_id', $courseId)
                        ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                        ->orderBy('start_date', 'desc')
                        ->get()
                        ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                        ->toArray();
                })
                ->searchable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable(),
                TextColumn::make('course.code')
                    ->label('Code'),
                TextColumn::make('intake.name')
                    ->label('Class / Intake')
                    ->badge()
                    ->color('info')
                    ->placeholder('No cohort assigned')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Enrolled At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completion & Certificate')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? 'Completed (' . $state->format('M d, Y') . ')' : 'In Progress'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Enrol in Course'),
            ])
            ->recordActions([
                Action::make('assign_intake')
                    ->label('Assign / Change Intake')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->modalHeading(fn (Enrollment $record): string => "Assign Intake Cohort for {$record->course?->title}")
                    ->form([
                        Select::make('course_intake_id')
                            ->label('Class / Intake')
                            ->placeholder('No specific cohort (General)')
                            ->options(fn (Enrollment $record): array => CourseIntake::query()
                                ->where('course_id', $record->course_id)
                                ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                ->orderBy('start_date', 'desc')
                                ->get()
                                ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                                ->toArray()
                            )
                            ->default(fn (Enrollment $record) => $record->course_intake_id)
                            ->searchable(),
                    ])
                    ->action(function (Enrollment $record, array $data): void {
                        $record->update(['course_intake_id' => $data['course_intake_id'] ?? null]);
                        $intakeName = $record->fresh()->intake?->name ?? 'General (No cohort)';
                        Notification::make()
                            ->title("Intake updated to '{$intakeName}'.")
                            ->success()
                            ->send();
                    }),

                Action::make('toggle_completion')
                    ->label(fn (Enrollment $record): string => $record->completed_at ? 'Reset Status' : 'Mark Complete')
                    ->icon(fn (Enrollment $record): string => $record->completed_at ? 'heroicon-o-arrow-path' : 'heroicon-o-check-circle')
                    ->color(fn (Enrollment $record): string => $record->completed_at ? 'gray' : 'success')
                    ->action(function (Enrollment $record): void {
                        if ($record->completed_at) {
                            $record->markAsIncomplete();
                            Certificate::query()
                                ->where('user_id', $record->user_id)
                                ->where('course_id', $record->course_id)
                                ->delete();

                            if ($record->user && $record->course) {
                                try {
                                    app(GamificationService::class)->revokeCourseCompleted($record->user, $record->course);
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            }

                            Notification::make()
                                ->title('Course completion reset and certificate/badges locked')
                                ->info()
                                ->send();
                        } else {
                            $record->markAsCompleted(auth()->user());
                            if ($record->user && $record->course) {
                                $certificate = app(CertificateService::class)->issue($record->user, $record->course, force: true);
                                if ($certificate && $certificate->wasRecentlyCreated) {
                                    try {
                                        $record->user->notify(new CertificateIssuedNotification($certificate));
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                }

                                try {
                                    app(GamificationService::class)->awardCourseCompleted($record->user, $record->course);
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            }
                            Notification::make()
                                ->title('Course Marked Complete!')
                                ->body('Certificate and completion badge are now ready for ' . ($record->user?->name ?? 'student') . '.')
                                ->success()
                                ->send();
                        }
                    }),
                DeleteAction::make()->label('Unenrol'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Unenrol Selected'),
                ]),
            ]);
    }
}
