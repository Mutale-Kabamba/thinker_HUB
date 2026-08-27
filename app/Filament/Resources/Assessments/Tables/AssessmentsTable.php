<?php

namespace App\Filament\Resources\Assessments\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssessmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Assessment')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('target_level')
                    ->label('Track')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Beginner' => 'success',
                        'Intermediate' => 'warning',
                        'Advanced' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Target')
                    ->placeholder('All Learners')
                    ->searchable(),
                TextColumn::make('date_given')
                    ->label('Assigned')
                    ->date('M d, Y')
                    ->sortable(),
                TextColumn::make('publish_at')
                    ->label('Publish At')
                    ->dateTime('M d, Y H:i')
                    ->placeholder('Immediate')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('M d, Y')
                    ->badge()
                    ->color(fn ($record): string => $record->due_date && $record->due_date->isPast() ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                    \Filament\Actions\Action::make('downloadSubmissionsZip')
                        ->label('Download Submissions (ZIP)')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('info')
                        ->visible(fn ($record) => $record->submissions()->exists())
                        ->action(function ($record) {
                            $submissions = $record->submissions()->with(['user', 'assessment'])->get();
                            $service = app(\App\Services\SubmissionZipService::class);
                            $slug = \Illuminate\Support\Str::slug($record->name ?: 'Assessment', '_');
                            $response = $service->downloadAssessmentsZip($submissions, "Submissions_{$slug}");

                            if (! $response) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No submission files found for this assessment.')
                                    ->warning()
                                    ->send();

                                return null;
                            }

                            return $response;
                        }),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('downloadSubmissionsZip')
                        ->label('Download Submissions (ZIP)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $submissions = \App\Models\AssessmentSubmission::query()
                                ->whereIn('assessment_id', $records->pluck('id'))
                                ->with(['user', 'assessment'])
                                ->get();
                            $service = app(\App\Services\SubmissionZipService::class);
                            $response = $service->downloadAssessmentsZip($submissions);

                            if (! $response) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No submission files found for selected assessments.')
                                    ->warning()
                                    ->send();

                                return null;
                            }

                            return $response;
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
