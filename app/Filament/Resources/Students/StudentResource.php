<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\CourseEnrolledNotification;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Students';

    protected static ?string $modelLabel = 'Student';

    protected static ?string $pluralModelLabel = 'Students';

    protected static ?string $slug = 'students';

    protected static string|UnitEnum|null $navigationGroup = 'PEOPLE & ROLES';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ])
                    ->placeholder('Select gender'),

                TextInput::make('nrc_passport')
                    ->label('NRC / Passport Number')
                    ->placeholder('e.g. 123456/11/1 or Passport No.')
                    ->maxLength(50),

                Select::make('courses')
                    ->label('Enrolled Courses')
                    ->relationship('courses', 'title')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Toggle::make('is_kid')
                    ->label('Junior Learner / Kid Account')
                    ->helperText('Enable if registering a minor student requiring parent management.')
                    ->live(),

                TextInput::make('unique_code')
                    ->label('Kid Unique Login Code')
                    ->default(fn () => User::generateKidUniqueCode())
                    ->helperText('Unique code child uses to sign in. Cannot be changed by the kid.')
                    ->visible(fn (callable $get): bool => (bool) $get('is_kid')),

                TextInput::make('age')
                    ->label('Kid Age')
                    ->numeric()
                    ->minValue(3)
                    ->maxValue(18)
                    ->required(fn (callable $get): bool => (bool) $get('is_kid'))
                    ->visible(fn (callable $get): bool => (bool) $get('is_kid')),

                TextInput::make('school')
                    ->label('School Attending')
                    ->placeholder('e.g. Baobab College / Lusaka Boys')
                    ->required(fn (callable $get): bool => (bool) $get('is_kid'))
                    ->visible(fn (callable $get): bool => (bool) $get('is_kid')),

                TextInput::make('grade')
                    ->label('Grade / Year')
                    ->placeholder('e.g. Grade 5')
                    ->required(fn (callable $get): bool => (bool) $get('is_kid'))
                    ->visible(fn (callable $get): bool => (bool) $get('is_kid')),

                Select::make('parents')
                    ->label('Linked Parent / Guardian')
                    ->relationship('parents', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Select an existing parent or click + to register parent/guardian details.')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Parent Full Name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Parent Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email'),
                        TextInput::make('phone_number')
                            ->label('WhatsApp / Phone')
                            ->required(),
                        TextInput::make('occupation')
                            ->label('Occupation')
                            ->required(),
                        TextInput::make('company')
                            ->label('Employer / Organization')
                            ->required(),
                        TextInput::make('address')
                            ->label('Home Address')
                            ->required(),
                        TextInput::make('password')
                            ->label('Parent Initial Password')
                            ->password()
                            ->default('Parent123!')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $parent = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'phone_number' => $data['phone_number'] ?? null,
                            'occupation' => $data['occupation'] ?? null,
                            'company' => $data['company'] ?? null,
                            'address' => $data['address'] ?? null,
                            'password' => \Illuminate\Support\Facades\Hash::make($data['password'] ?? 'Parent123!'),
                            'role' => 'parent',
                            'default_portal' => 'parent',
                            'is_active' => true,
                            'email_verified_at' => now(),
                        ]);

                        return $parent->id;
                    })
                    ->visible(fn (callable $get): bool => (bool) $get('is_kid')),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'female' => 'danger',
                        'male' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('nrc_passport')
                    ->label('NRC / Passport')
                    ->searchable()
                    ->placeholder('—')
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
                TextColumn::make('courses_count')
                    ->label('Courses')
                    ->counts('courses')
                    ->formatStateUsing(function ($state, $record) {
                        $codes = $record->courses()->pluck('code')->filter()->implode(', ');

                        return $codes ? "{$state} ({$codes})" : (string) $state;
                    })
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('assignment_submissions_count')
                    ->label('Submissions')
                    ->counts('assignmentSubmissions')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not verified')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'student'))
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
                    ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('enrollments', fn (Builder $q) => $q->where('course_id', $data['value']));
                    }),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->defaultSort('name')
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    ViewAction::make()->icon('heroicon-m-eye'),
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('enroll_in_course')
                        ->label('Enroll in Course')
                        ->icon('heroicon-o-academic-cap')
                        ->form([
                            Select::make('course_id')
                                ->label('Course')
                                ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
                                ->required()
                                ->searchable()
                                ->live(),

                            Select::make('course_intake_id')
                                ->label('Class / Intake (Cohort)')
                                ->placeholder('Select intake cohort (Optional)')
                                ->options(function (Get $get): array {
                                    $courseId = $get('course_id');
                                    if (! $courseId) {
                                        return [];
                                    }

                                    return \App\Models\CourseIntake::query()
                                        ->where('course_id', $courseId)
                                        ->where('status', '!=', \App\Models\CourseIntake::STATUS_ARCHIVED)
                                        ->orderBy('start_date', 'desc')
                                        ->get()
                                        ->mapWithKeys(fn (\App\Models\CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                                        ->toArray();
                                })
                                ->searchable(),

                            Radio::make('fee_type')
                                ->label('Fee Type')
                                ->options([
                                    'paid' => 'Paying Students',
                                    'free' => 'Free / Sponsored (Scholarship / Waiver)',
                                ])
                                ->default('paid')
                                ->live()
                                ->required(),

                            TextInput::make('fee_amount')
                                ->label('Fee Amount per Student (ZMW)')
                                ->numeric()
                                ->prefix('ZMW')
                                ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                                ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                            Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'bank_transfer' => 'Bank Transfer / Deposit',
                                    'cash' => 'Cash Payment',
                                    'mobile_money' => 'Mobile Money',
                                    'card' => 'Card / POS',
                                    'other' => 'Other',
                                ])
                                ->default('bank_transfer')
                                ->visible(fn (Get $get): bool => $get('fee_type') === 'paid')
                                ->required(fn (Get $get): bool => $get('fee_type') === 'paid'),

                            TextInput::make('transaction_reference')
                                ->label('Batch Transaction Reference / Receipt #')
                                ->placeholder('e.g. BATCH-REC-2026')
                                ->visible(fn (Get $get): bool => $get('fee_type') === 'paid'),

                            Textarea::make('payment_notes')
                                ->label('Accounting Remarks')
                                ->placeholder('e.g. Batch enrolled via admin panel')
                                ->rows(2),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $course = Course::query()->findOrFail((int) $data['course_id']);
                            $isFree = ($data['fee_type'] ?? 'paid') === 'free';
                            $amount = $isFree ? 0.00 : (float) ($data['fee_amount'] ?? 0);
                            $method = $isFree ? 'free_registration' : ($data['payment_method'] ?? 'bank_transfer');
                            $batchRef = trim((string) ($data['transaction_reference'] ?? ''));
                            $notes = trim((string) ($data['payment_notes'] ?? ''));

                            $created = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                $ref = $batchRef !== '' ? "{$batchRef}-{$record->id}" : ($isFree ? 'FREE-' . strtoupper(Str::random(8)) : 'REC-' . strtoupper(Str::random(8)));

                                $enrollment = Enrollment::query()->firstOrCreate(
                                    [
                                        'user_id' => $record->id,
                                        'course_id' => $course->id,
                                    ],
                                    [
                                        'course_intake_id' => $data['course_intake_id'] ?? null,
                                        'registration_type' => Enrollment::CHANNEL_ADMIN_MANUAL,
                                        'registered_by_user_id' => auth()->id(),
                                        'payment_status' => $isFree ? Enrollment::STATUS_FREE : Enrollment::STATUS_PAID,
                                        'is_free' => $isFree,
                                        'fee_amount' => $amount,
                                        'payment_method' => $method,
                                        'transaction_reference' => $ref,
                                        'payment_notes' => $notes,
                                        'paid_at' => $isFree ? null : now(),
                                    ]
                                );

                                if ($enrollment->wasRecentlyCreated) {
                                    $created++;

                                    \App\Models\Payment::create([
                                        'user_id' => $record->id,
                                        'course_id' => $course->id,
                                        'enrollment_id' => $enrollment->id,
                                        'amount' => $amount,
                                        'original_amount' => $course->fees ?? $amount,
                                        'discount_amount' => $isFree ? ($course->fees ?? 0) : 0,
                                        'currency' => 'ZMW',
                                        'payment_method' => $method,
                                        'provider' => $isFree ? 'Tuition Waiver' : 'Batch Admin Entry',
                                        'reference' => $ref,
                                        'status' => 'paid',
                                        'is_free' => $isFree,
                                        'registration_type' => Enrollment::CHANNEL_ADMIN_MANUAL,
                                        'registered_by_user_id' => auth()->id(),
                                        'payment_notes' => $notes,
                                        'paid_at' => $isFree ? null : now(),
                                    ]);

                                    try {
                                        $record->notify(new CourseEnrolledNotification($course));
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                } else {
                                    $skipped++;
                                }
                            }

                            $notification = Notification::make()
                                ->title('Bulk enrollment complete')
                                ->body($created.' enrolled, '.$skipped.' skipped (already enrolled).');

                            if ($created > 0) {
                                $notification->success()->send();
                            } else {
                                $notification->warning()->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            Students\RelationManagers\EnrollmentsRelationManager::class,
            Students\RelationManagers\AssignmentSubmissionsRelationManager::class,
            Students\RelationManagers\AssessmentSubmissionsRelationManager::class,
            Students\RelationManagers\QuizAttemptsRelationManager::class,
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
