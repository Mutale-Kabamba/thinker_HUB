<?php

namespace App\Filament\Resources\Assessments\Tables;

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
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('target_level')
                    ->label('Level')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Target User')
                    ->searchable(),
                TextColumn::make('date_given')
                    ->date()
                    ->sortable(),
                TextColumn::make('publish_at')
                    ->label('Publish At')
                    ->dateTime()
                    ->placeholder('Immediate')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('downloadSubmissionsZip')
                    ->label('Download Submissions (ZIP)')
                    ->icon('heroicon-o-arrow-down-tray')
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
