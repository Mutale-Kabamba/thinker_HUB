<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Assessment;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Quiz Details')
                    ->columns(2)
                    ->schema([
                        Select::make('assessment_id')
                            ->label('Assessment')
                            ->required()
                            ->searchable()
                            ->options(
                                fn (): array => Assessment::query()
                                    ->whereDoesntHave('quiz')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (Assessment $a) => [
                                        $a->id => $a->name . ' (' . ($a->course?->title ?? 'No Course') . ')',
                                    ])
                                    ->toArray()
                            )
                            ->helperText('Only assessments without a quiz are shown.')
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('time_limit_minutes')
                            ->label('Time Limit (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(300)
                            ->placeholder('No time limit'),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('pass_percentage')
                            ->label('Pass Percentage')
                            ->numeric()
                            ->default(50)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),

                        Toggle::make('shuffle_questions')
                            ->label('Shuffle Questions')
                            ->default(false),

                        Toggle::make('show_results')
                            ->label('Show Results After Completion')
                            ->default(true),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Section::make('Questions')
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

                                Textarea::make('question')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),

                                TextInput::make('points')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(100),

                                Textarea::make('explanation')
                                    ->label('Explanation (shown after answering)')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Repeater::make('options')
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('option_text')
                                            ->label('Option')
                                            ->required()
                                            ->maxLength(500),

                                        Checkbox::make('is_correct')
                                            ->label('Correct Answer'),
                                    ])
                                    ->columns(2)
                                    ->minItems(2)
                                    ->maxItems(6)
                                    ->defaultItems(4)
                                    ->visible(fn (callable $get): bool => $get('type') === 'multiple_choice')
                                    ->columnSpanFull()
                                    ->reorderable()
                                    ->collapsible(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => ($state['question'] ?? null)
                                ? \Illuminate\Support\Str::limit($state['question'], 60)
                                : 'New Question'
                            )
                            ->columnSpanFull()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, int $index): array {
                                $data['sort_order'] = $index;

                                return $data;
                            }),
                    ]),
            ]);
    }
}
