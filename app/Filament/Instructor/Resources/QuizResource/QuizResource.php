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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuizResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = Quiz::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

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
                                ? Str::limit($state['question'], 60)
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),
                TextColumn::make('time_limit_minutes')
                    ->label('Time Limit')
                    ->formatStateUsing(fn (?int $state): string => $state ? $state . ' min' : 'No limit')
                    ->sortable(),
                TextColumn::make('pass_percentage')
                    ->label('Pass %')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->counts('attempts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
