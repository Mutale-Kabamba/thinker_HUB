<?php

namespace App\Filament\Instructor\Resources\StudentResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\StudentResource\Pages\CreateStudent;
use App\Filament\Instructor\Resources\StudentResource\Pages\EditStudent;
use App\Filament\Instructor\Resources\StudentResource\Pages\ListStudents;
use App\Filament\Instructor\Resources\StudentResource\Pages\ViewStudent;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Students';

    protected static ?string $modelLabel = 'Student';

    protected static ?string $pluralModelLabel = 'Students';

    protected static ?string $slug = 'students';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->password()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->minLength(8),

            Select::make('track')
                ->label('Level / Track')
                ->options([
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                ])
                ->required()
                ->default('Beginner'),

            Select::make('courses')
                ->label('Enrol in My Courses')
                ->relationship('courses', 'title')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn (): array => static::instructorCourseOptions()),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        $courseIds = static::instructorCourseIds();

        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('track')
                    ->label('Level')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Beginner' => 'info',
                        'Intermediate' => 'warning',
                        'Advanced' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('enrolled_courses')
                    ->label('Enrolled Courses')
                    ->getStateUsing(function (User $record) use ($courseIds): string {
                        return (string) $record->courses()
                            ->whereIn('courses.id', $courseIds)
                            ->count();
                    })
                    ->badge()
                    ->color('primary'),
                TextColumn::make('assignment_submissions_avg')
                    ->label('Avg Assignment Grade')
                    ->getStateUsing(function (User $record) use ($courseIds): string {
                        $avg = $record->assignmentSubmissions()
                            ->whereNotNull('grade')
                            ->whereHas('assignment', fn (Builder $q) => $q->whereIn('course_id', $courseIds))
                            ->avg('grade');

                        return $avg !== null ? round($avg, 1) . '%' : '—';
                    })
                    ->alignCenter(),
                TextColumn::make('assessment_submissions_avg')
                    ->label('Avg Assessment Score')
                    ->getStateUsing(function (User $record) use ($courseIds): string {
                        $avg = $record->assessmentSubmissions()
                            ->whereNotNull('score')
                            ->whereHas('assessment', fn (Builder $q) => $q->whereIn('course_id', $courseIds))
                            ->avg('score');

                        return $avg !== null ? round($avg, 1) . '%' : '—';
                    })
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function (Builder $query) use ($courseIds): Builder {
                return $query
                    ->where('role', 'student')
                    ->whereHas('enrollments', fn (Builder $q) => $q->whereIn('course_id', $courseIds));
            })
            ->filters([
                SelectFilter::make('track')
                    ->label('Level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ]),
                SelectFilter::make('course')
                    ->label('Course')
                    ->options(fn (): array => static::instructorCourseOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('enrollments', fn (Builder $q) => $q->where('course_id', $data['value']));
                    }),
            ])
            ->defaultSort('name')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StudentResource\RelationManagers\EnrollmentsRelationManager::class,
            StudentResource\RelationManagers\AssignmentSubmissionsRelationManager::class,
            StudentResource\RelationManagers\AssessmentSubmissionsRelationManager::class,
            StudentResource\RelationManagers\QuizAttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'view' => ViewStudent::route('/{record}'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
