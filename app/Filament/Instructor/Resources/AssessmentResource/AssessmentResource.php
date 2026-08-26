<?php

namespace App\Filament\Instructor\Resources\AssessmentResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\AssessmentResource\Pages\CreateAssessment;
use App\Filament\Instructor\Resources\AssessmentResource\Pages\EditAssessment;
use App\Filament\Instructor\Resources\AssessmentResource\Pages\ListAssessments;
use App\Models\Assessment;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
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

class AssessmentResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = Assessment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'GRADING & EVALUATIONS';

    protected static ?string $navigationLabel = 'Assessments';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name of Assessment')
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
                    ->helperText('Leave empty to send to all cohorts across the course.'),

                Select::make('target_level')
                    ->label('Target Track / Level')
                    ->required()
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ])
                    ->live(),

                Select::make('user_id')
                    ->label('Target User')
                    ->required()
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
                    ->dehydrateStateUsing(fn (mixed $state): mixed => $state === 'all' ? null : $state)
                    ->helperText('Choose All Students to send to all students in the selected course and level.'),

                FileUpload::make('file_paths')
                    ->label('Assessment Document(s)')
                    ->disk('public')
                    ->directory('assessments')
                    ->multiple()
                    ->reorderable()
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
                        'image/*',
                        'application/zip',
                    ])
                    ->helperText('Attach one or more assessment files, question papers, or briefs.')
                    ->columnSpanFull(),

                DatePicker::make('date_given')
                    ->label('Date Given')
                    ->required()
                    ->default(now()),

                DateTimePicker::make('publish_at')
                    ->label('Publish At')
                    ->helperText('Leave empty to publish immediately. Set a future date/time to auto-release to students.'),

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
                TextColumn::make('user.name')
                    ->label('Target User')
                    ->placeholder('All in course + level')
                    ->searchable(),
                TextColumn::make('date_given')
                    ->date()
                    ->sortable(),
                TextColumn::make('publish_at')
                    ->label('Publish At')
                    ->dateTime()
                    ->placeholder('Immediate')
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
                \Filament\Actions\Action::make('downloadSubmissionsZip')
                    ->label('Download Submissions (ZIP)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn ($record) => $record->submissions()->exists())
                    ->action(function ($record) {
                        $submissions = $record->submissions()->with(['user', 'assessment'])->get();
                        $service = app(\App\Services\SubmissionZipService::class);
                        $slug = \Illuminate\Support\Str::slug($record->name ?: 'Assessment', '_');
                        $response = $service->downloadAssessmentsZip($submissions, "Submissions_{$slug}");

                        if (! $response) {
                            \Filament\Notifications\Notification::make()
                                ->title('No submission files found for this assessment.')
                                ->warning()
                                ->send();

                            return null;
                        }

                        return $response;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('downloadSubmissionsZip')
                        ->label('Download Submissions (ZIP)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $submissions = \App\Models\AssessmentSubmission::query()
                                ->whereIn('assessment_id', $records->pluck('id'))
                                ->with(['user', 'assessment'])
                                ->get();
                            $service = app(\App\Services\SubmissionZipService::class);
                            $response = $service->downloadAssessmentsZip($submissions);

                            if (! $response) {
                                \Filament\Notifications\Notification::make()
                                ->title('No submission files found for selected assessments.')
                                ->warning()
                                ->send();

                                return null;
                            }

                            return $response;
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessments::route('/'),
            'create' => CreateAssessment::route('/create'),
            'edit' => EditAssessment::route('/{record}/edit'),
        ];
    }
}
