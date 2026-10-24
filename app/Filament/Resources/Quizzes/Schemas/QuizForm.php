<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\CourseIntake;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class QuizForm
{
    /**
     * @param  (callable(): array<string, string>)|null  $courseOptions
     */
    public static function configure(Schema $schema, ?callable $courseOptions = null): Schema
    {
        $courseSelect = Select::make('course_id')
            ->label('Course')
            ->required()
            ->searchable()
            ->live();

        if ($courseOptions !== null) {
            $courseSelect->options($courseOptions)
                ->helperText('Only your assigned courses are shown.');
        } else {
            $courseSelect->relationship('course', 'title')
                ->helperText('Select the course this quiz belongs to.');
        }

        return $schema
            ->components([
                Section::make('Evaluation Parameters')
                    ->description('Quiz targeting, timing thresholds, scoring criteria, and delivery conditions.')
                    ->icon('heroicon-o-adjustments-vertical')
                    ->schema([
                        $courseSelect,

                        Select::make('course_intake_id')
                            ->label('Target Intake / Class Cohort')
                            ->nullable()
                            ->searchable()
                            ->options(function (callable $get): array {
                                $courseId = $get('course_id');
                                if (! $courseId) {
                                    return [];
                                }

                                return CourseIntake::query()
                                    ->where('course_id', $courseId)
                                    ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                    ->orderBy('start_date', 'desc')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->placeholder('All Intakes / Entire Course')
                            ->helperText('Leave empty to make quiz available across all cohorts.'),

                        TextInput::make('title')
                            ->label('Quiz Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Module 3 Assessment: Async Programming'),

                        TextInput::make('time_limit_minutes')
                            ->label('Time Limit (min)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(300)
                            ->placeholder('No limit (open time)'),

                        TextInput::make('pass_percentage')
                            ->label('Passing Score')
                            ->numeric()
                            ->default(50)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),

                        DateTimePicker::make('publish_at')
                            ->label('Scheduled Publication')
                            ->helperText('Auto-releases at this time (leave empty for instant release).'),

                        Textarea::make('description')
                            ->label('Instructions for Students')
                            ->rows(3)
                            ->placeholder('Rules, calculators allowed, special instructions, review policies...')
                            ->columnSpanFull(),

                        Toggle::make('shuffle_questions')
                            ->label('Shuffle Questions')
                            ->helperText('Randomize question sequence per attempt.')
                            ->default(false),

                        Toggle::make('show_results')
                            ->label('Immediate Results')
                            ->helperText('Show score and answer keys upon submission.')
                            ->default(true),

                        Toggle::make('is_active')
                            ->label('Active / Published')
                            ->helperText('Visible to students if within publication dates.')
                            ->default(true),
                    ])
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                Section::make('Questions & Answer Keys')
                    ->description('Author assessment items, scoring weight, and interactive multiple-choice options.')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Repeater::make('questions')
                            ->relationship()
                            ->schema([
                                Select::make('type')
                                    ->options([
                                        'multiple_choice' => 'Multiple Choice',
                                        'theory' => 'Theory (Written Answer)',
                                        'practical' => 'Practical (Code/Task)',
                                    ])
                                    ->required()
                                    ->live()
                                    ->default('multiple_choice'),

                                TextInput::make('points')
                                    ->label('Points')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(100),

                                Textarea::make('question')
                                    ->label('Question Statement')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('State the question clearly...')
                                    ->columnSpanFull(),

                                Textarea::make('explanation')
                                    ->label('Explanation & Learning Notes')
                                    ->helperText('Shown to students in review mode after answering.')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Repeater::make('options')
                                    ->relationship()
                                    ->orderColumn('sort_order')
                                    ->schema([
                                        TextInput::make('option_text')
                                            ->label('Option Text')
                                            ->required()
                                            ->maxLength(500)
                                            ->placeholder('Option answer choice...'),

                                        Checkbox::make('is_correct')
                                            ->label('Correct Answer'),
                                    ])
                                    ->columns(['default' => 1, 'sm' => 2])
                                    ->minItems(2)
                                    ->maxItems(6)
                                    ->defaultItems(4)
                                    ->visible(fn (callable $get): bool => $get('type') === 'multiple_choice')
                                    ->columnSpanFull()
                                    ->reorderable()
                                    ->collapsible(),
                            ])
                            ->columns(['default' => 1, 'md' => 2])
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->orderColumn('sort_order')
                            ->itemLabel(fn (array $state): ?string => ($state['question'] ?? null)
                                ? Str::limit($state['question'], 60)
                                : 'New Question'
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
