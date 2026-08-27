<?php

namespace App\Filament\Instructor\Resources\StudentResource\StudentResource\RelationManagers;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentSubmissionsRelationManager extends RelationManager
{
    use ScopedToInstructor;

    protected static string $relationship = 'assignmentSubmissions';

    protected static ?string $title = 'Assignment Submissions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assignment.name')
                    ->label('Assignment')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('assignment.course.title')
                    ->label('Course'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Graded' => 'success',
                        'Submitted' => 'warning',
                        'Returned' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('grade')
                    ->numeric()
                    ->suffix('/100')
                    ->placeholder('—'),
                TextColumn::make('is_retake')
                    ->label('Attempt')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->is_retake ? '2nd Try' : ($record->retake_allowed ? '2nd Try Open' : '1st Try'))
                    ->color(fn ($record) => $record->is_retake ? 'info' : ($record->retake_allowed ? 'success' : 'gray')),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('grantRetake')
                        ->label('Grant 2nd Try')
                        ->icon('heroicon-m-arrow-path')
                        ->color('success')
                        ->visible(fn ($record) => ! $record->retake_allowed)
                        ->requiresConfirmation()
                        ->modalHeading('Grant Second Chance')
                        ->modalDescription('Allow the student to submit a second attempt. Any recorded grade above 50% will be capped at the 50% passing mark.')
                        ->action(function ($record) {
                            $record->grantRetake(auth()->user());
                            \Filament\Notifications\Notification::make()->title('Second chance granted.')->success()->send();
                        }),
                    \Filament\Actions\Action::make('revokeRetake')
                        ->label('Revoke 2nd Try')
                        ->icon('heroicon-m-x-mark')
                        ->color('danger')
                        ->visible(fn ($record) => (bool) $record->retake_allowed)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->revokeRetake();
                            \Filament\Notifications\Notification::make()->title('Second chance revoked.')->info()->send();
                        }),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'assignment',
                    fn (Builder $q) => $q->whereIn('course_id', static::instructorCourseIds())
                )
            )
            ->defaultSort('submitted_at', 'desc');
    }
}
