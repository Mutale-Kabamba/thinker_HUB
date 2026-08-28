<?php

namespace App\Filament\Instructor\Resources\QuizResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\QuizResource\Pages\CreateQuiz;
use App\Filament\Instructor\Resources\QuizResource\Pages\EditQuiz;
use App\Filament\Instructor\Resources\QuizResource\Pages\ListQuizzes;
use App\Models\Course;
use App\Models\Quiz;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuizResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = Quiz::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|\UnitEnum|null $navigationGroup = 'GRADING & EVALUATIONS';

    protected static ?string $navigationLabel = 'Quizzes';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        $courseIds = static::instructorCourseIds();

        return $schema
            ->components([
                Section::make('Quiz Details')
                    ->columns(2)
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->required()
                            ->searchable()
                            ->options(
                                fn (): array => Course::query()
                                    ->whereIn('id', $courseIds)
                                    ->orderBy('title')
                                    ->get()
                                    ->mapWithKeys(fn (Course $c) => [
                                        $c->id => $c->title . ' (' . $c->code . ')',
                                    ])
                                    ->toArray()
                            )
                            ->helperText('Only your assigned courses are shown.')
                            ->live()
                            ->columnSpanFull(),

                        Select::make('course_intake_id')
                            ->label('Target Intake / Class')
                            ->nullable()
                            ->searchable()
                            ->options(function (callable $get): array {
                                $courseId = $get('course_id');
                                if (! $courseId) {
                                    return [];
                                }

                                return \App\Models\CourseIntake::query()
                                    ->where('course_id', $courseId)
                                    ->where('status', '!=', \App\Models\CourseIntake::STATUS_ARCHIVED)
                                    ->orderBy('start_date', 'desc')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->placeholder('All Intakes / Entire Course')
                            ->helperText('Leave empty to make quiz available to all cohorts across the course.')
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

                        DateTimePicker::make('publish_at')
                            ->label('Publish At')
                            ->helperText('Leave empty to publish immediately. Set a future date/time to auto-release to students.')
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
                                    ->orderColumn('sort_order')
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
                            ->orderColumn('sort_order')
                            ->itemLabel(fn (array $state): ?string => ($state['question'] ?? null)
                                ? Str::limit($state['question'], 60)
                                : 'New Question'
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                // Mobile Card View Structure (Stacked & Clean)
                Stack::make([
                    Split::make([
                        Stack::make([
                            TextColumn::make('title')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('course.title')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        IconColumn::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('questions_count')
                            ->label('Questions')
                            ->counts('questions')
                            ->formatStateUsing(fn ($state) => "{$state} Questions")
                            ->badge()
                            ->color('info')
                            ->size('xs'),
                        TextColumn::make('pass_percentage')
                            ->label('Pass %')
                            ->formatStateUsing(fn ($state) => "Pass: {$state}%")
                            ->badge()
                            ->color('success')
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('time_limit_minutes')
                    ->label('Time Limit')
                    ->formatStateUsing(fn (?int $state): string => $state ? $state . ' min' : 'No limit')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('pass_percentage')
                    ->label('Pass %')
                    ->suffix('%')
                    ->sortable()
                    ->visibleFrom('md'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->visibleFrom('md'),
                TextColumn::make('publish_at')
                    ->label('Publish At')
                    ->dateTime()
                    ->placeholder('Immediate')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->counts('attempts')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds())
            )
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuizzes::route('/'),
            'create' => CreateQuiz::route('/create'),
            'edit' => EditQuiz::route('/{record}/edit'),
        ];
    }
}
