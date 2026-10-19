<?php

namespace App\Filament\Actions;

use App\Models\Course;
use App\Models\Quiz;
use App\Services\QuizExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExportQuizzesAction
{
    /**
     * Build the export action for admin (all courses, no instructor restriction).
     */
    public static function make(): Action
    {
        return static::buildAction(courseIds: null);
    }

    /**
     * Build the export action scoped to specific course IDs (instructor panel).
     */
    public static function makeForCourses(Collection|array $courseIds): Action
    {
        return static::buildAction(courseIds: collect($courseIds)->all());
    }

    /**
     * Build a table bulk action to export selected quizzes.
     */
    public static function makeBulkAction(?array $courseIds = null): BulkAction
    {
        return BulkAction::make('exportSelectedQuizzes')
            ->label('Export Selected')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->modalHeading('Export Selected Quizzes')
            ->modalDescription('Export the selected quizzes as a JSON backup (re-importable) or CSV spreadsheet.')
            ->form([
                Select::make('format')
                    ->label('Export Format')
                    ->options([
                        'json' => 'JSON (Full backup & re-importable)',
                        'csv' => 'CSV (Spreadsheet Question Bank)',
                    ])
                    ->default('json')
                    ->required(),
            ])
            ->action(function (Collection $records, array $data) use ($courseIds) {
                if ($records->isEmpty()) {
                    return null;
                }

                if ($courseIds !== null) {
                    $records = $records->filter(fn (Quiz $q) => in_array($q->course_id, $courseIds, true));
                }

                if ($records->isEmpty()) {
                    Notification::make()
                        ->title('No quizzes available')
                        ->body('None of the selected quizzes belong to your assigned courses.')
                        ->warning()
                        ->send();

                    return null;
                }

                $format = (string) ($data['format'] ?? 'json');
                $filename = 'Quizzes_Selected_' . date('Ymd_His') . '.' . ($format === 'csv' ? 'csv' : 'json');

                return app(QuizExportService::class)->downloadResponse($records, $format, $filename);
            });
    }

    /**
     * Build a single record action to export an individual quiz.
     */
    public static function makeRowAction(): Action
    {
        return Action::make('exportQuiz')
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->modalHeading(fn (Quiz $record): string => "Export Quiz: {$record->title}")
            ->modalDescription('Export this single quiz as a JSON file (re-importable) or CSV spreadsheet.')
            ->form([
                Select::make('format')
                    ->label('Export Format')
                    ->options([
                        'json' => 'JSON (Full backup & re-importable)',
                        'csv' => 'CSV (Spreadsheet Question Bank)',
                    ])
                    ->default('json')
                    ->required(),
            ])
            ->action(function (Quiz $record, array $data) {
                $record->loadMissing(['course', 'questions.options']);
                $format = (string) ($data['format'] ?? 'json');
                $slug = Str::slug($record->title, '_');
                $filename = "Quiz_{$slug}_" . date('Ymd') . '.' . ($format === 'csv' ? 'csv' : 'json');

                return app(QuizExportService::class)->downloadResponse(collect([$record]), $format, $filename);
            });
    }

    /**
     * @param  array|null  $courseIds  Null = no scope (admin)
     */
    private static function buildAction(?array $courseIds): Action
    {
        return Action::make('exportQuizzes')
            ->label('Export Quizzes')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->modalHeading('Export Quizzes')
            ->modalDescription(
                'Export all quizzes, or filter by course. Choose between JSON (full backup, re-importable) and CSV (spreadsheet question bank).'
            )
            ->form([
                Select::make('format')
                    ->label('Export Format')
                    ->options([
                        'json' => 'JSON (Full backup & re-importable)',
                        'csv' => 'CSV (Spreadsheet Question Bank)',
                    ])
                    ->default('json')
                    ->required(),

                Select::make('course_id')
                    ->label('Filter by Course')
                    ->placeholder($courseIds === null ? 'All Courses' : 'All Assigned Courses')
                    ->options(function () use ($courseIds): array {
                        $query = Course::query();

                        if ($courseIds !== null) {
                            $query->whereIn('id', $courseIds);
                        } else {
                            $query->whereHas('quizzes');
                        }

                        return $query->orderBy('title')
                            ->get()
                            ->mapWithKeys(fn (Course $c) => [
                                $c->id => $c->title . ' (' . $c->code . ')',
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText('Leave empty to export quizzes across all courses.'),

                Toggle::make('only_active')
                    ->label('Export only active quizzes')
                    ->default(false),
            ])
            ->action(function (array $data) use ($courseIds) {
                $format = (string) ($data['format'] ?? 'json');
                $courseId = ! empty($data['course_id']) ? (int) $data['course_id'] : null;
                $onlyActive = (bool) ($data['only_active'] ?? false);

                $query = Quiz::query()->with(['course', 'questions.options']);

                if ($courseIds !== null) {
                    $query->whereIn('course_id', $courseIds);
                }

                if ($courseId !== null) {
                    if ($courseIds !== null && ! in_array($courseId, $courseIds, true)) {
                        Notification::make()
                            ->title('Unauthorized course selected')
                            ->danger()
                            ->send();

                        return null;
                    }

                    $query->where('course_id', $courseId);
                }

                if ($onlyActive) {
                    $query->where('is_active', true);
                }

                $quizzes = $query->orderBy('course_id')->orderBy('title')->get();

                if ($quizzes->isEmpty()) {
                    Notification::make()
                        ->title('No quizzes found')
                        ->body('There are no quizzes matching your selected export criteria.')
                        ->warning()
                        ->send();

                    return null;
                }

                $courseCode = null;
                if ($courseId && $first = $quizzes->first()) {
                    $courseCode = $first->course?->code;
                }

                $prefix = $courseCode ? "Quizzes_{$courseCode}" : 'Quizzes_Export';
                $filename = "{$prefix}_" . date('Ymd_His') . '.' . ($format === 'csv' ? 'csv' : 'json');

                return app(QuizExportService::class)->downloadResponse($quizzes, $format, $filename);
            });
    }
}
