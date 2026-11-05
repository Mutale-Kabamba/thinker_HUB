<?php

namespace App\Filament\Instructor\Resources\QuizResource;

use App\Filament\Actions\ExportQuizzesAction;
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
        return \App\Filament\Resources\Quizzes\Schemas\QuizForm::configure(
            $schema,
            fn (): array => Course::query()
                ->whereIn('id', static::instructorCourseIds())
                ->orderBy('title')
                ->get()
                ->mapWithKeys(fn (Course $c) => [
                    $c->id => $c->title . ' (' . $c->code . ')',
                ])
                ->toArray()
        );
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
                TextColumn::make('publish_at')
                    ->label('Publish At')
                    ->dateTime()
                    ->placeholder('Immediate')
                    ->sortable(),
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
                \Filament\Actions\ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square'),
                    ExportQuizzesAction::makeRowAction(),
                    \Filament\Actions\DeleteAction::make()
                        ->icon('heroicon-m-trash'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportQuizzesAction::makeBulkAction(static::instructorCourseIds()),
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
