<?php

namespace App\Filament\Instructor\Resources\StudentResource\StudentResource\RelationManagers;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizAttemptsRelationManager extends RelationManager
{
    use ScopedToInstructor;

    protected static string $relationship = 'quizAttempts';

    protected static ?string $title = 'Quiz Attempts';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('quiz.course.title')
                    ->label('Course'),
                TextColumn::make('score')
                    ->numeric()
                    ->suffix(fn ($record): string => '/' . ($record->total_points ?? '?')),
                TextColumn::make('percentage')
                    ->suffix('%')
                    ->color(fn ($record): string => ($record->passed ?? false) ? 'success' : 'danger'),
                IconColumn::make('passed')
                    ->boolean(),
                TextColumn::make('is_retake')
                    ->label('Attempt')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->is_retake ? '2nd Try' : ($record->retake_allowed ? '2nd Try Open' : '1st Try'))
                    ->color(fn ($record) => $record->is_retake ? 'info' : ($record->retake_allowed ? 'success' : 'gray')),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('In progress'),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('viewAnswers')
                    ->label('View Answers')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => $record->completed_at !== null)
                    ->modalHeading(fn ($record) => 'Quiz Breakdown & Answers: ' . ($record->quiz?->title ?? 'Quiz Attempt'))
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.instructor.modals.quiz-attempt-answers', [
                        'attempt' => $record->loadMissing(['quiz.questions.options', 'quiz.course', 'answers.option', 'answers.question', 'user']),
                    ])),
                \Filament\Actions\Action::make('grantRetake')
                    ->label('Grant 2nd Try')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->retake_allowed && $record->completed_at !== null)
                    ->requiresConfirmation()
                    ->modalHeading('Grant Second Chance')
                    ->modalDescription('Allow this student to retake the quiz. On their second attempt, the recorded mark will be capped at the passing mark.')
                    ->action(function ($record) {
                        $record->grantRetake(auth()->user());
                        \Filament\Notifications\Notification::make()->title('Second chance granted.')->success()->send();
                    }),
                \Filament\Actions\Action::make('revokeRetake')
                    ->label('Revoke 2nd Try')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => (bool) $record->retake_allowed)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->revokeRetake();
                        \Filament\Notifications\Notification::make()->title('Second chance revoked.')->info()->send();
                    }),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'quiz',
                    fn (Builder $q) => $q->whereIn('course_id', static::instructorCourseIds())
                )
            )
            ->defaultSort('started_at', 'desc');
    }
}
