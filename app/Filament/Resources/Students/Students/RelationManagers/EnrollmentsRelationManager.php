<?php

namespace App\Filament\Resources\Students\Students\RelationManagers;

use App\Models\Certificate;
use App\Models\Course;
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
