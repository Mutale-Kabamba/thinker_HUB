<?php

namespace App\Filament\Instructor\Resources\StudentResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\StudentResource\Pages\CreateStudent;
use App\Filament\Instructor\Resources\StudentResource\Pages\EditStudent;
use App\Filament\Instructor\Resources\StudentResource\Pages\ListStudents;
use App\Filament\Instructor\Resources\StudentResource\Pages\ViewStudent;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
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
use Illuminate\Database\Eloquent\Model;

class StudentResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'PEOPLE & ROLES';

    protected static ?string $navigationLabel = 'Students';

    protected static ?string $modelLabel = 'Student';

    protected static ?string $pluralModelLabel = 'Students';

    protected static ?string $slug = 'students';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && $user->isInstructor();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return (bool) $user && $user->isInstructor();
    }

    public static function canView(Model $record): bool
    {
        return $record instanceof User && static::canAccessStudentRecord($record);
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof User && static::canAccessStudentRecord($record);
    }

    public static function canDelete(Model $record): bool
    {
        return $record instanceof User && static::canAccessStudentRecord($record);
    }

    protected static function canAccessStudentRecord(User $record): bool
    {
        $user = auth()->user();

        if (! $user || ! $user->isInstructor() || $record->role !== 'student') {
            return false;
        }

        $courseIds = static::instructorCourseIds();

        if (empty($courseIds)) {
            return false;
        }

        return $record->enrollments()->whereIn('course_id', $courseIds)->exists();
    }

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
                \Filament\Actions\Action::make('award_gamification')
                    ->label('Award XP & Badges')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->modalHeading(fn (User $record): string => "Award XP & Badges to {$record->name}")
                    ->modalDescription('Recognize off-platform achievements such as classroom presentations, hackathon wins, live participation, leadership, or custom achievements.')
                    ->modalSubmitActionLabel('Award Reward')
                    ->form([
                        Select::make('course_id')
                            ->label('Associated Course')
                            ->options(fn (): array => static::instructorCourseOptions())
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Select the course tied to this activity (Optional).'),

                        Select::make('activity_type')
                            ->label('Activity / Reason')
                            ->options([
                                'Outstanding Presentation' => '🎤 Outstanding Presentation',
                                'Classroom Participation & Debate' => '💬 Classroom Participation & Debate',
                                'Project Demo & Showcase' => '💻 Project Demo & Showcase',
                                'Hackathon / Competition Winner' => '🚀 Hackathon / Competition Winner',
                                'Peer Mentoring & Collaboration' => '🤝 Peer Mentoring & Collaboration',
                                'Lab Practical Excellence' => '⚙️ Lab Practical Excellence',
                                'Leadership & Teamwork' => '👑 Leadership & Teamwork',
                                'Extracurricular Contribution' => '🌟 Extracurricular Contribution',
                                'custom' => '➕ Other / Custom Activity',
                            ])
                            ->required()
                            ->default('Outstanding Presentation')
                            ->live(),

                        TextInput::make('custom_activity_name')
                            ->label('Custom Activity Name')
                            ->placeholder('e.g. AI Prompt Engineering Challenge')
                            ->visible(fn ($get) => $get('activity_type') === 'custom')
                            ->required(fn ($get) => $get('activity_type') === 'custom')
                            ->maxLength(100),

                        TextInput::make('xp')
                            ->label('XP Points to Award')
                            ->numeric()
                            ->required()
                            ->default(50)
                            ->minValue(1)
                            ->maxValue(2000)
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('coins', (int) round(((float) $state) * 0.30));
                            })
                            ->helperText('Contributes to the student lifetime XP and Rank Tier progression.'),

                        TextInput::make('coins')
                            ->label('Thinker Coins (TC)')
                            ->numeric()
                            ->nullable()
                            ->default(15)
                            ->minValue(0)
                            ->helperText('Spendable coins for Claim Hub rewards (defaults to 30% of XP).'),

                        Select::make('badge_id')
                            ->label('Award Badge (Optional)')
                            ->options(function () {
                                return \App\Models\Badge::query()
                                    ->get()
                                    ->mapWithKeys(fn (\App\Models\Badge $b) => [$b->id => "{$b->icon} {$b->name} (+{$b->xp_reward} XP)"])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Select a badge to grant (Optional)')
                            ->helperText('Optionally award a permanent recognition badge to the student profile.'),

                        Toggle::make('award_badge_xp')
                            ->label("Also grant Badge's inherent bonus XP reward")
                            ->default(false)
                            ->visible(fn ($get) => filled($get('badge_id'))),

                        \Filament\Forms\Components\Textarea::make('note')
                            ->label('Commendation Note / Reason')
                            ->placeholder('e.g. Delivered an exceptional presentation on Neural Networks with live code demonstrations.')
                            ->rows(3)
                            ->maxLength(500)
                            ->nullable(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $instructor = auth()->user();
                        if (! $instructor) {
                            return;
                        }

                        $activityName = $data['activity_type'] === 'custom'
                            ? ($data['custom_activity_name'] ?? 'Special Recognition')
                            : $data['activity_type'];

                        $course = ! empty($data['course_id']) ? \App\Models\Course::find($data['course_id']) : null;
                        $xp = (int) ($data['xp'] ?? 0);
                        $coins = isset($data['coins']) && $data['coins'] !== '' ? (int) $data['coins'] : null;
                        $badgeId = $data['badge_id'] ?? null;
                        $awardBadgeXp = (bool) ($data['award_badge_xp'] ?? false);
                        $note = $data['note'] ?? null;

                        $result = app(\App\Services\GamificationService::class)->awardManualInstructorReward(
                            instructor: $instructor,
                            student: $record,
                            course: $course,
                            activityName: $activityName,
                            xp: $xp,
                            coins: $coins,
                            badgeKeyOrId: $badgeId,
                            awardBadgeXp: $awardBadgeXp,
                            note: $note
                        );

                        if ($result['success'] ?? false) {
                            \Filament\Notifications\Notification::make()
                                ->title('Recognition Awarded!')
                                ->body("Successfully awarded +{$result['xp']} XP & +{$result['coins']} TC" . ($result['badge'] ? " and the '{$result['badge']}' badge" : '') . " to {$record->name}.")
                                ->success()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkAction::make('bulk_award_gamification')
                    ->label('Award XP & Badges')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->modalHeading('Award XP & Badges to Selected Students')
                    ->modalDescription('Recognize off-platform achievements for all selected students simultaneously (e.g. group project presentations, hackathon teams, debate participants).')
                    ->modalSubmitActionLabel('Award All Selected')
                    ->form([
                        Select::make('course_id')
                            ->label('Associated Course')
                            ->options(fn (): array => static::instructorCourseOptions())
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('activity_type')
                            ->label('Activity / Reason')
                            ->options([
                                'Outstanding Presentation' => '🎤 Outstanding Presentation',
                                'Classroom Participation & Debate' => '💬 Classroom Participation & Debate',
                                'Project Demo & Showcase' => '💻 Project Demo & Showcase',
                                'Hackathon / Competition Winner' => '🚀 Hackathon / Competition Winner',
                                'Peer Mentoring & Collaboration' => '🤝 Peer Mentoring & Collaboration',
                                'Lab Practical Excellence' => '⚙️ Lab Practical Excellence',
                                'Leadership & Teamwork' => '👑 Leadership & Teamwork',
                                'Extracurricular Contribution' => '🌟 Extracurricular Contribution',
                                'custom' => '➕ Other / Custom Activity',
                            ])
                            ->required()
                            ->default('Outstanding Presentation')
                            ->live(),

                        TextInput::make('custom_activity_name')
                            ->label('Custom Activity Name')
                            ->visible(fn ($get) => $get('activity_type') === 'custom')
                            ->required(fn ($get) => $get('activity_type') === 'custom'),

                        TextInput::make('xp')
                            ->label('XP Points per Student')
                            ->numeric()
                            ->required()
                            ->default(50)
                            ->minValue(1)
                            ->maxValue(2000)
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('coins', (int) round(((float) $state) * 0.30));
                            }),

                        TextInput::make('coins')
                            ->label('Thinker Coins (TC) per Student')
                            ->numeric()
                            ->nullable()
                            ->default(15)
                            ->minValue(0),

                        Select::make('badge_id')
                            ->label('Award Badge (Optional)')
                            ->options(function () {
                                return \App\Models\Badge::query()
                                    ->get()
                                    ->mapWithKeys(fn (\App\Models\Badge $b) => [$b->id => "{$b->icon} {$b->name} (+{$b->xp_reward} XP)"])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Toggle::make('award_badge_xp')
                            ->label("Also grant Badge's inherent bonus XP reward")
                            ->default(false)
                            ->visible(fn ($get) => filled($get('badge_id'))),

                        \Filament\Forms\Components\Textarea::make('note')
                            ->label('Commendation Note / Reason')
                            ->rows(3)
                            ->maxLength(500)
                            ->nullable(),
                    ])
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                        $instructor = auth()->user();
                        if (! $instructor) {
                            return;
                        }

                        $activityName = $data['activity_type'] === 'custom'
                            ? ($data['custom_activity_name'] ?? 'Special Recognition')
                            : $data['activity_type'];

                        $course = ! empty($data['course_id']) ? \App\Models\Course::find($data['course_id']) : null;
                        $xp = (int) ($data['xp'] ?? 0);
                        $coins = isset($data['coins']) && $data['coins'] !== '' ? (int) $data['coins'] : null;
                        $badgeId = $data['badge_id'] ?? null;
                        $awardBadgeXp = (bool) ($data['award_badge_xp'] ?? false);
                        $note = $data['note'] ?? null;

                        $count = 0;
                        foreach ($records as $student) {
                            if ($student instanceof User && $student->isStudent()) {
                                app(\App\Services\GamificationService::class)->awardManualInstructorReward(
                                    instructor: $instructor,
                                    student: $student,
                                    course: $course,
                                    activityName: $activityName,
                                    xp: $xp,
                                    coins: $coins,
                                    badgeKeyOrId: $badgeId,
                                    awardBadgeXp: $awardBadgeXp,
                                    note: $note
                                );
                                $count++;
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Bulk Recognition Awarded!')
                            ->body("Successfully awarded +{$xp} XP to {$count} selected students.")
                            ->success()
                            ->send();
                    }),
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
