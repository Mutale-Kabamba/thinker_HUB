<?php

namespace App\Filament\Actions;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportSessionsAction
{
    /**
     * Build the import action for admin (all courses).
     */
    public static function make(): Action
    {
        return static::buildAction(courseIds: null, instructorId: null);
    }

    /**
     * Build the import action scoped to specific course IDs (instructor panel).
     */
    public static function makeForInstructor(Collection|array $courseIds, int $instructorId): Action
    {
        return static::buildAction(courseIds: collect($courseIds)->all(), instructorId: $instructorId);
    }

    private static function buildAction(?array $courseIds, ?int $instructorId): Action
    {
        return Action::make('importSessions')
            ->label('Import Sessions (JSON)')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->modalHeading('Bulk Import Course Sessions')
            ->modalDescription(
                'Upload a JSON file containing an array of session objects. '
                . 'Each session needs a course_code matching an existing course. '
                . 'Duplicate sessions (same course + date + start_time + type) will be updated.'
            )
            ->form([
                FileUpload::make('json_file')
                    ->label('JSON File')
                    ->disk('local')
                    ->directory('imports/sessions')
                    ->acceptedFileTypes(['application/json', 'text/json'])
                    ->maxSize(2048)
                    ->required(),
            ])
            ->action(function (array $data) use ($courseIds, $instructorId): void {
                $filePath = (string) ($data['json_file'] ?? '');

                if ($filePath === '' || ! Storage::disk('local')->exists($filePath)) {
                    Notification::make()
                        ->title('Import failed')
                        ->body('Uploaded file could not be found. Please try again.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $raw = Storage::disk('local')->get($filePath);

                    Log::info('Session import: file read.', ['path' => $filePath, 'bytes' => strlen($raw)]);

                    $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

                    if (! is_array($decoded)) {
                        throw new \RuntimeException('JSON root must be an array of session objects.');
                    }

                    $created = 0;
                    $updated = 0;
                    $skipped = 0;
                    $errors = [];

                    foreach ($decoded as $index => $row) {
                        if (! is_array($row)) {
                            $skipped++;
                            continue;
                        }

                        try {
                            $result = static::processRow($row, $index, $courseIds, $instructorId);

                            match ($result) {
                                'created' => $created++,
                                'updated' => $updated++,
                                default => $skipped++,
                            };
                        } catch (Throwable $e) {
                            $skipped++;
                            $errors[] = "Row {$index}: {$e->getMessage()}";
                        }
                    }

                    $body = "Created: {$created} | Updated: {$updated} | Skipped: {$skipped}";

                    if (count($errors) > 0) {
                        $body .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 15));

                        if (count($errors) > 15) {
                            $body .= "\n... and " . (count($errors) - 15) . ' more.';
                        }
                    }

                    Notification::make()
                        ->title('Session import completed')
                        ->body($body)
                        ->success()
                        ->send();
                } catch (Throwable $exception) {
                    report($exception);

                    Notification::make()
                        ->title('Import failed')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                } finally {
                    Storage::disk('local')->delete($filePath);
                }
            });
    }

    // ---------------------------------------------------------------
    //  Process a single session row
    // ---------------------------------------------------------------

    private static function processRow(array $row, int $index, ?array $courseIds, ?int $instructorId): string
    {
        // --- Resolve course ---
        $course = static::resolveCourse($row, $courseIds);

        if (! $course) {
            $tried = static::pickString($row, ['course_code', 'courseCode', 'course']);
            $hint = $tried !== ''
                ? "Course code '{$tried}' not found."
                : 'Missing course_code.';

            throw new \RuntimeException($hint);
        }

        // --- Required fields ---
        $sessionDate = static::pickString($row, ['session_date', 'date']);
        if ($sessionDate === '') {
            throw new \RuntimeException('Missing required field: session_date');
        }

        if (! strtotime($sessionDate)) {
            throw new \RuntimeException("Invalid date: '{$sessionDate}'. Use YYYY-MM-DD format.");
        }

        $startTime = static::pickString($row, ['start_time', 'startTime', 'start']);
        if ($startTime === '') {
            throw new \RuntimeException('Missing required field: start_time');
        }

        $endTime = static::pickString($row, ['end_time', 'endTime', 'end']);
        if ($endTime === '') {
            throw new \RuntimeException('Missing required field: end_time');
        }

        // --- Type ---
        $rawType = static::pickString($row, ['type']);
        $type = static::normalizeType($rawType);

        // --- Student (for one-on-one) ---
        $studentId = null;
        if ($type === 'one_on_one') {
            $studentEmail = static::pickString($row, ['student_email', 'studentEmail', 'student']);
            if ($studentEmail !== '') {
                $student = User::query()->where('email', $studentEmail)->first();
                if (! $student) {
                    throw new \RuntimeException("Student email '{$studentEmail}' not found.");
                }
                $studentId = $student->id;
            }
        }

        // --- Status ---
        $rawStatus = static::pickString($row, ['status']);
        $status = static::normalizeStatus($rawStatus);

        // --- Build payload ---
        $payload = [
            'course_id' => $course->id,
            'instructor_id' => $instructorId,
            'type' => $type,
            'student_id' => $studentId,
            'title' => static::nullableString(Arr::get($row, 'title')),
            'session_date' => $sessionDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
            'notes' => static::nullableString(Arr::get($row, 'notes')),
        ];

        // --- Upsert (match on course + date + start_time + type) ---
        return DB::transaction(function () use ($course, $sessionDate, $startTime, $type, $payload): string {
            $existing = CourseSession::query()
                ->where('course_id', $course->id)
                ->where('session_date', $sessionDate)
                ->where('start_time', $startTime)
                ->where('type', $type)
                ->first();

            if ($existing) {
                $existing->fill($payload)->save();

                return 'updated';
            }

            CourseSession::query()->create($payload);

            Log::info('Session import: created session.', [
                'course' => $course->code,
                'date' => $sessionDate,
                'start' => $startTime,
            ]);

            return 'created';
        });
    }

    // ---------------------------------------------------------------
    //  Course resolution
    // ---------------------------------------------------------------

    private static function resolveCourse(array $row, ?array $courseIds): ?Course
    {
        $code = static::pickString($row, ['course_code', 'courseCode', 'course']);

        if ($code !== '') {
            $query = Course::query()->whereRaw('LOWER(code) = ?', [mb_strtolower($code)]);

            if ($courseIds !== null) {
                $query->whereIn('id', $courseIds);
            }

            $result = $query->first();
            if ($result) {
                return $result;
            }
        }

        $title = static::pickString($row, ['course_title', 'course_name', 'courseName']);

        if ($title !== '') {
            $query = Course::query()->whereRaw('LOWER(title) = ?', [mb_strtolower($title)]);

            if ($courseIds !== null) {
                $query->whereIn('id', $courseIds);
            }

            return $query->first();
        }

        return null;
    }

    // ---------------------------------------------------------------
    //  Normalisers
    // ---------------------------------------------------------------

    private static function normalizeType(string $type): string
    {
        return match (mb_strtolower($type)) {
            'one_on_one', 'one-on-one', 'oneonone', '1on1', 'individual', 'private' => 'one_on_one',
            default => 'group',
        };
    }

    private static function normalizeStatus(string $status): string
    {
        return match (mb_strtolower($status)) {
            'completed' => 'completed',
            'rescheduled' => 'rescheduled',
            'cancelled', 'canceled' => 'cancelled',
            default => 'scheduled',
        };
    }

    // ---------------------------------------------------------------
    //  Helpers
    // ---------------------------------------------------------------

    private static function pickString(array $data, array $keys): string
    {
        foreach ($keys as $key) {
            $val = trim((string) Arr::get($data, $key, ''));
            if ($val !== '') {
                return $val;
            }
        }

        return '';
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
