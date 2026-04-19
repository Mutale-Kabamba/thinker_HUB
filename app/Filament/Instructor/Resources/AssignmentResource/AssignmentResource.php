<?php

namespace App\Filament\Instructor\Resources\AssignmentResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\AssignmentResource\Pages\CreateAssignment;
use App\Filament\Instructor\Resources\AssignmentResource\Pages\EditAssignment;
use App\Filament\Instructor\Resources\AssignmentResource\Pages\ListAssignments;
use App\Models\Assignment;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = Assignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Assignments';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name of Assignment')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('course_id')
                    ->label('Course')
                    ->required()
                    ->searchable()
                    ->options(fn (): array => static::instructorCourseOptions())
                    ->live(),

                Select::make('target_level')
                    ->label('Target Track / Level')
                    ->required()
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ])
                    ->live(),

                Select::make('target_user_id')
                    ->label('Target User')
                    ->searchable()
                    ->options(function (callable $get): array {
                        $courseId = $get('course_id');
                        $level = $get('target_level');
                        $options = ['all' => 'All Students'];

                        if (! $courseId || ! $level) {
                            return $options;
                        }

                        $students = User::query()
                            ->where('role', 'student')
                            ->where('track', $level)
                            ->whereHas('courses', fn ($q) => $q->where('courses.id', $courseId))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();

                        return $options + $students;
                    })
                    ->default('all')
                    ->required()
                    ->dehydrateStateUsing(fn (mixed $state): mixed => $state === 'all' ? null : $state)
                    ->helperText('Choose All Students to send to all students in the selected course and level.'),

                FileUpload::make('file_path')
                    ->label('File Upload')
                    ->disk('public')
                    ->directory('assignments')
                    ->maxSize(10240)
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/plain',
                        'text/csv',
                    ])
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->columnSpanFull(),

                DatePicker::make('date_given')
                    ->label('Date Given')
                    ->required()
                    ->default(now()),

                DatePicker::make('due_date')
                    ->label('Due Date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('target_level')
                    ->label('Level')
                    ->searchable(),
                TextColumn::make('targetUser.name')
                    ->label('Target User')
                    ->placeholder('All in course + level')
                    ->searchable(),
                TextColumn::make('date_given')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds()))
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
            'index' => ListAssignments::route('/'),
            'create' => CreateAssignment::route('/create'),
            'edit' => EditAssignment::route('/{record}/edit'),
        ];
    }
}
