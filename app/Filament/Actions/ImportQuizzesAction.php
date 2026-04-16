<?php

namespace App\Filament\Actions;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportQuizzesAction
{
    /**
     * Build the import action for admin (all courses).
     */
    public static function make(): Action
    {
        return static::buildAction(courseIds: null);
    }

    /**
     * Build the import action scoped to specific course IDs (instructor panel).
     */
    public static function makeForCourses(Collection|array $courseIds): Action
    {
        return static::buildAction(courseIds: collect($courseIds)->all());
    }

    /**
     * @param  array|null  $courseIds  Null = no scope (admin)
     */
    private static function buildAction(?array $courseIds): Action
    {
        return Action::make('importQuizzes')
            ->label('Import Quizzes (JSON)')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->modalHeading('Import Quizzes from JSON')
            ->modalDescription(
                'Upload a JSON file with an array of quizzes. Each quiz needs a course_code (or assessment_name + course_code). '
                . 'If the assessment doesn\'t exist it will be auto-created. '
                . 'Supports multiple_choice, theory, and practical question types.'
            )
            ->form([
                FileUpload::make('json_file')
                    ->label('JSON File')
                    ->disk('local')
                    ->directory('imports/quizzes')
                    ->acceptedFileTypes(['application/json', 'text/json'])
                    ->maxSize(2048)
                    ->required(),
                Toggle::make('replace_questions')
                    ->label('Replace existing questions on update')
                    ->helperText('When updating an existing quiz, delete old questions and import new ones. If off, questions are left untouched on update.')
                    ->default(true),
            ])
            ->action(function (array $data) use ($courseIds): void {
                $filePath = (string) ($data['json_file'] ?? '');
                $replaceQuestions = (bool) ($data['replace_questions'] ?? true);

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

                    Log::info('Quiz import: file read.', ['path' => $filePath, 'bytes' => strlen($raw)]);

                    $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

                    if (! is_array($decoded)) {
                        throw new \RuntimeException('JSON root must be an array of quizzes.');
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
                            $result = static::processQuizRow($row, $index, $courseIds, $replaceQuestions);

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
                        $body .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10));

                        if (count($errors) > 10) {
                            $body .= "\n... and " . (count($errors) - 10) . ' more.';
                        }
                    }

                    Notification::make()
                        ->title('Quiz import completed')
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
    //  Process a single quiz row
    // ---------------------------------------------------------------

    private static function processQuizRow(array $row, int $index, ?array $courseIds, bool $replaceQuestions): string
    {
        $title = trim((string) Arr::get($row, 'title', ''));

        if ($title === '') {
            throw new \RuntimeException('Missing required field: title');
        }

        // Resolve (or auto-create) the assessment
        $assessment = static::resolveAssessment($row, $courseIds);

        if (! $assessment) {
            $triedCode = static::pickString($row, ['course_code', 'courseCode', 'course']);
            $hint = $triedCode !== ''
                ? "Course code '{$triedCode}' not found in database."
                : 'Provide at least a course_code matching an existing course.';

            throw new \RuntimeException($hint . ' Row keys: ' . implode(', ', array_keys($row)));
        }

        $questions = Arr::get($row, 'questions', []);

        if (! is_array($questions) || count($questions) === 0) {
            throw new \RuntimeException('Quiz must have at least one question.');
        }

        static::validateQuestions($questions);

        $quizPayload = [
            'assessment_id' => $assessment->id,
            'title' => $title,
            'description' => static::nullableString(Arr::get($row, 'description')),
            'time_limit_minutes' => static::nullablePositiveInt(Arr::get($row, 'time_limit_minutes')),
            'shuffle_questions' => (bool) Arr::get($row, 'shuffle_questions', false),
            'show_results' => (bool) Arr::get($row, 'show_results', true),
            'pass_percentage' => min(100, max(0, (int) Arr::get($row, 'pass_percentage', 50))),
            'is_active' => (bool) Arr::get($row, 'is_active', true),
        ];

        return DB::transaction(function () use ($assessment, $quizPayload, $questions, $replaceQuestions): string {
            $existing = Quiz::query()
                ->where('assessment_id', $assessment->id)
                ->first();

            if ($existing) {
                $existing->fill($quizPayload)->save();

                if ($replaceQuestions) {
                    $existing->questions()->delete();
                    static::createQuestions($existing, $questions);
                }

                return 'updated';
            }

            $quiz = Quiz::query()->create($quizPayload);
            static::createQuestions($quiz, $questions);

            return 'created';
        });
    }

    // ---------------------------------------------------------------
    //  Assessment & course resolution
    // ---------------------------------------------------------------

    private static function resolveAssessment(array $row, ?array $courseIds): ?Assessment
    {
        // 1. Try direct assessment_id
        $assessmentId = Arr::get($row, 'assessment_id');

        if ($assessmentId) {
            $query = Assessment::query()->where('id', $assessmentId);

            if ($courseIds !== null) {
                $query->whereIn('course_id', $courseIds);
            }

            $result = $query->first();

            if ($result) {
                return $result;
            }

            Log::warning('Quiz import: assessment_id not found.', ['assessment_id' => $assessmentId]);
        }

        // 2. Gather flexible field names
        $assessmentName = static::pickString($row, ['assessment_name', 'assessment', 'assessmentName']);
        $courseCode = static::pickString($row, ['course_code', 'courseCode', 'course']);

        // 3. Try by assessment name + course code
        if ($assessmentName !== '' && $courseCode !== '') {
            $result = Assessment::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($assessmentName)])
                ->whereHas('course', fn ($q) => $q->whereRaw('LOWER(code) = ?', [mb_strtolower($courseCode)]))
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_id', $courseIds))
                ->first();

            if ($result) {
                return $result;
            }
        }

        // 4. Try by assessment name only
        if ($assessmentName !== '') {
            $result = Assessment::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($assessmentName)])
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_id', $courseIds))
                ->first();

            if ($result) {
                return $result;
            }
        }

        // 5. Try by course code — pick first assessment without a quiz
        if ($courseCode !== '') {
            $result = Assessment::query()
                ->whereHas('course', fn ($q) => $q->whereRaw('LOWER(code) = ?', [mb_strtolower($courseCode)]))
                ->whereDoesntHave('quiz')
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_id', $courseIds))
                ->orderBy('name')
                ->first();

            if ($result) {
                return $result;
            }
        }

        // 6. Auto-create assessment if we can resolve the course
        $course = static::resolveCourse($row, $courseIds);

        if ($course) {
            $name = $assessmentName !== ''
                ? $assessmentName
                : trim((string) Arr::get($row, 'title', 'Imported Assessment'));

            $assessment = Assessment::query()->create([
                'name' => $name,
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'description' => 'Auto-created during quiz import.',
            ]);

            Log::info('Quiz import: auto-created assessment.', [
                'id' => $assessment->id,
                'name' => $name,
                'course' => $course->code,
            ]);

            return $assessment;
        }

        Log::warning('Quiz import: no assessment or course resolved.', [
            'keys' => array_keys($row),
            'assessment_name' => $assessmentName,
            'course_code' => $courseCode,
        ]);

        return null;
    }

    private static function resolveCourse(array $row, ?array $courseIds): ?Course
    {
        // By code
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

        // By title
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
    //  Question validation & creation
    // ---------------------------------------------------------------

    private static function validateQuestions(array $questions): void
    {
        foreach ($questions as $i => $q) {
            if (! is_array($q)) {
                throw new \RuntimeException("Question #{$i}: must be an object.");
            }

            $rawType = trim((string) Arr::get($q, 'type', ''));
            $type = static::normalizeQuestionType($rawType);

            if ($type === null) {
                throw new \RuntimeException("Question #{$i}: invalid type '{$rawType}'. Must be multiple_choice, theory, or practical.");
            }

            $questionText = static::pickString($q, ['question', 'question_text', 'text']);

            if ($questionText === '') {
                throw new \RuntimeException("Question #{$i}: missing question text.");
            }

            if ($type === 'multiple_choice') {
                $options = Arr::get($q, 'options', Arr::get($q, 'choices', []));

                if (! is_array($options) || count($options) < 2) {
                    throw new \RuntimeException("Question #{$i}: multiple_choice needs at least 2 options.");
                }

                if (count($options) > 6) {
                    throw new \RuntimeException("Question #{$i}: multiple_choice can have at most 6 options.");
                }

                $hasCorrect = false;

                foreach ($options as $opt) {
                    if (is_array($opt) && ! empty(Arr::get($opt, 'is_correct', Arr::get($opt, 'correct', false)))) {
                        $hasCorrect = true;
                        break;
                    }
                }

                if (! $hasCorrect) {
                    throw new \RuntimeException("Question #{$i}: multiple_choice needs at least one correct option.");
                }
            }
        }
    }

    private static function normalizeQuestionType(string $type): ?string
    {
        return match (mb_strtolower($type)) {
            'multiple_choice', 'multiple-choice', 'multiplechoice', 'mcq', 'mc', 'choice', 'multi' => 'multiple_choice',
            'theory', 'essay', 'written', 'text', 'short_answer', 'short-answer', 'long_answer' => 'theory',
            'practical', 'code', 'coding', 'programming', 'task' => 'practical',
            default => null,
        };
    }

    private static function createQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $sortOrder => $q) {
            $type = static::normalizeQuestionType(trim((string) Arr::get($q, 'type', '')));
            $questionText = static::pickString($q, ['question', 'question_text', 'text']);

            $question = QuizQuestion::query()->create([
                'quiz_id' => $quiz->id,
                'type' => $type,
                'question' => $questionText,
                'explanation' => static::nullableString(Arr::get($q, 'explanation')),
                'points' => max(1, (int) Arr::get($q, 'points', 1)),
                'sort_order' => (int) Arr::get($q, 'sort_order', $sortOrder),
            ]);

            if ($type === 'multiple_choice') {
                $options = Arr::get($q, 'options', Arr::get($q, 'choices', []));

                foreach ($options as $optOrder => $opt) {
                    if (! is_array($opt)) {
                        continue;
                    }

                    $optionText = static::pickString($opt, ['option_text', 'text', 'label', 'option']);

                    QuizOption::query()->create([
                        'quiz_question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => (bool) (Arr::get($opt, 'is_correct') ?? Arr::get($opt, 'correct', false)),
                        'sort_order' => (int) Arr::get($opt, 'sort_order', $optOrder),
                    ]);
                }
            }
        }
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

    private static function nullablePositiveInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
