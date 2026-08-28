<?php

namespace App\Filament\Resources\Assignments\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Assignment')
                    ->searchable()
                    ->grow()
                    ->wrap()
                    ->weight('bold'),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('sm'),
                TextColumn::make('target_level')
                    ->label('Track')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Beginner' => 'success',
                        'Intermediate' => 'warning',
                        'Advanced' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('targetUser.name')
                    ->label('Target')
                    ->placeholder('All Learners')
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('date_given')
                    ->label('Assigned')
                    ->date('M d, Y')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('publish_at')
                    ->label('Publish At')
                    ->dateTime('M d, Y H:i')
                    ->placeholder('Immediate')
                    ->sortable()
                    ->visibleFrom('xl'),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('M d, Y')
                    ->badge()
                    ->color(fn ($record): string => $record->due_date && $record->due_date->isPast() ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->visibleFrom('xl')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->visibleFrom('xl')
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
                            $submissions = $record->submissions()->with(['user', 'assignment'])->get();
                            $service = app(\App\Services\SubmissionZipService::class);
                            $slug = \Illuminate\Support\Str::slug($record->name, '_');
                            $response = $service->downloadAssignmentsZip($submissions, "Submissions_{$slug}");

                            if (! $response) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No submission files found for this assignment.')
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
                            $submissions = \App\Models\AssignmentSubmission::query()
                                ->whereIn('assignment_id', $records->pluck('id'))
                                ->with(['user', 'assignment'])
                                ->get();
                            $service = app(\App\Services\SubmissionZipService::class);
                            $response = $service->downloadAssignmentsZip($submissions);

                            if (! $response) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No submission files found for selected assignments.')
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
