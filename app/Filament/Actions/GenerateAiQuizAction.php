<?php

namespace App\Filament\Actions;

use App\Ai\Agents\QuizGeneratorAgent;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Document;
use Throwable;

class GenerateAiQuizAction
{
    /**
     * Build the AI quiz generation action for admin (all courses).
     */
    public static function make(): Action
    {
        return static::buildAction(courseIds: null);
    }

    /**
     * Build the AI quiz generation action scoped to instructor courses.
     */
    public static function makeForCourses(Collection|array $courseIds): Action
    {
        return static::buildAction(courseIds: collect($courseIds)->all());
    }

    /**
     * @param  array|null  $courseIds  Null = all courses (admin)
     */
    private static function buildAction(?array $courseIds): Action
    {
        return Action::make('generateAiQuiz')
            ->label('Generate AI Quiz')
            ->icon('heroicon-o-sparkles')
            ->color('primary')
            ->modalHeading('Generate Quiz with AI')
            ->modalDescription(
                'Generate curriculum-aligned quiz questions using the AI engine from course topics or learning materials.'
            )
            ->modalSubmitActionLabel('Generate Quiz')
            ->form([
                Select::make('course_id')
                    ->label('Course')
                    ->options(function () use ($courseIds): array {
                        $query = Course::query();

                        if ($courseIds !== null) {
                            $query->whereIn('id', $courseIds);
                        }

                        return $query->orderBy('title')
                            ->get()
                            ->mapWithKeys(fn (Course $c) => [
                                $c->id => $c->title . ' (' . $c->code . ')',
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($set): void {
                        $set('course_intake_id', null);
                        $set('learning_material_id', null);
                    })
                    ->required(),

                Select::make('course_intake_id')
                    ->label('Cohort / Intake')
                    ->placeholder('All Intakes (General)')
                    ->options(function ($get): array {
                        $courseId = $get('course_id');

                        if (! $courseId) {
                            return [];
                        }

                        return CourseIntake::query()
                            ->where('course_id', $courseId)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable(),

                Select::make('learning_material_id')
                    ->label('Reference Material (PDF / Document)')
                    ->placeholder('None (Generate from Topic prompt only)')
                    ->options(function ($get): array {
                        $courseId = $get('course_id');

                        if (! $courseId) {
                            return [];
                        }

                        return LearningMaterial::query()
                            ->where('course_id', $courseId)
                            ->whereNotNull('file_path')
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText('Optionally attach an existing course document or PDF to ground quiz generation.'),

                TextInput::make('topic')
                    ->label('Topic / Focus Area')
                    ->placeholder('e.g. Module 3: Asynchronous JavaScript & Event Loop')
                    ->required()
                    ->maxLength(255),

                TextInput::make('question_count')
                    ->label('Question Count')
                    ->numeric()
                    ->default(5)
                    ->minValue(1)
                    ->maxValue(20)
                    ->required(),

                CheckboxList::make('question_types')
                    ->label('Question Types')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'theory' => 'Theory (Conceptual)',
                        'practical' => 'Practical (Coding / Lab)',
                    ])
                    ->default(['multiple_choice', 'theory'])
                    ->required(),

                Toggle::make('is_active')
                    ->label('Publish immediately')
                    ->helperText('Leave off to keep quiz as draft for review before publishing.')
                    ->default(false),
            ])
            ->action(function (array $data, Action $action) use ($courseIds): void {
                $courseId = (int) ($data['course_id'] ?? 0);

                if ($courseIds !== null && ! in_array($courseId, $courseIds, true)) {
                    Notification::make()
                        ->title('Unauthorized course')
                        ->body('You are not authorized to generate quizzes for this course.')
                        ->danger()
                        ->send();

                    return;
                }

                $course = Course::find($courseId);

                if (! $course) {
                    Notification::make()
                        ->title('Course not found')
                        ->body('The selected course could not be found.')
                        ->danger()
                        ->send();

                    return;
                }

                $topic = trim((string) ($data['topic'] ?? ''));
                $count = max(1, min(20, (int) ($data['question_count'] ?? 5)));
                $selectedTypes = (array) ($data['question_types'] ?? ['multiple_choice', 'theory']);
                $typesStr = implode(', ', $selectedTypes);

                $attachments = [];

                if (! empty($data['learning_material_id'])) {
                    $material = LearningMaterial::query()
                        ->where('id', $data['learning_material_id'])
                        ->where('course_id', $course->id)
                        ->first();

                    if ($material && $material->file_path && Storage::disk('public')->exists($material->file_path)) {
                        $attachments[] = Document::fromStorage($material->file_path, disk: 'public');
                    }
                }

                $prompt = <<<PROMPT
Generate a comprehensive quiz for the course "{$course->title}" ({$course->code}).
Topic / Focus: {$topic}
Total Questions: {$count}
Allowed Question Types: {$typesStr}

Requirements:
1. Create exactly {$count} questions strictly using the allowed types: {$typesStr}.
2. For multiple_choice questions, provide exactly 4 options with exactly one is_correct: true.
3. For theory and practical questions, provide clear, comprehensive grading rubrics and reference solutions in the explanation field.
4. Provide a descriptive quiz title and summary description.
PROMPT;

                try {
                    $response = QuizGeneratorAgent::make()->prompt($prompt, attachments: $attachments);
                    $payload = $response->toArray();

                    $quiz = DB::transaction(function () use ($course, $data, $payload, $topic): Quiz {
                        $quizTitle = trim((string) ($payload['title'] ?? ''));

                        if ($quizTitle === '') {
                            $quizTitle = "{$topic} Quiz";
                        }

                        $quiz = Quiz::create([
                            'course_id' => $course->id,
                            'course_intake_id' => ! empty($data['course_intake_id']) ? (int) $data['course_intake_id'] : null,
                            'title' => $quizTitle,
                            'description' => ! empty($payload['description']) ? trim((string) $payload['description']) : null,
                            'pass_percentage' => 50,
                            'is_active' => (bool) ($data['is_active'] ?? false),
                        ]);

                        $questions = Arr::get($payload, 'questions', []);

                        foreach ($questions as $order => $q) {
                            $rawType = trim((string) ($q['type'] ?? 'theory'));
                            $type = match (mb_strtolower($rawType)) {
                                'multiple_choice', 'mcq' => 'multiple_choice',
                                'practical', 'code' => 'practical',
                                default => 'theory',
                            };

                            $questionText = trim((string) ($q['question'] ?? ''));

                            if ($questionText === '') {
                                continue;
                            }

                            $question = QuizQuestion::create([
                                'quiz_id' => $quiz->id,
                                'type' => $type,
                                'question' => $questionText,
                                'explanation' => ! empty($q['explanation']) ? trim((string) $q['explanation']) : null,
                                'points' => max(1, (int) ($q['points'] ?? 1)),
                                'sort_order' => $order + 1,
                            ]);

                            if ($type === 'multiple_choice' && ! empty($q['options']) && is_array($q['options'])) {
                                foreach ($q['options'] as $optOrder => $opt) {
                                    $optText = trim((string) ($opt['option_text'] ?? ''));

                                    if ($optText === '') {
                                        continue;
                                    }

                                    QuizOption::create([
                                        'quiz_question_id' => $question->id,
                                        'option_text' => $optText,
                                        'is_correct' => (bool) ($opt['is_correct'] ?? false),
                                        'sort_order' => $optOrder + 1,
                                    ]);
                                }
                            }
                        }

                        return $quiz;
                    });

                    Notification::make()
                        ->title('Quiz generated successfully')
                        ->body("Created quiz '{$quiz->title}' with {$quiz->questions()->count()} questions.")
                        ->success()
                        ->send();

                    // Redirect to edit page
                    $editUrl = null;

                    try {
                        $panelId = filament()->getCurrentPanel()?->getId();

                        if ($panelId === 'instructor') {
                            $editUrl = \App\Filament\Instructor\Resources\QuizResource\QuizResource::getUrl('edit', ['record' => $quiz]);
                        } else {
                            $editUrl = \App\Filament\Resources\Quizzes\QuizResource::getUrl('edit', ['record' => $quiz]);
                        }
                    } catch (Throwable) {
                        $editUrl = null;
                    }

                    if ($editUrl) {
                        $action->redirect($editUrl);
                    }
                } catch (Throwable $e) {
                    Log::error('Quiz generation failed: ' . $e->getMessage(), ['exception' => $e]);

                    Notification::make()
                        ->title('Quiz generation failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}

