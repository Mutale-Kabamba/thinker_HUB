<?php

namespace App\Filament\Instructor\Resources\CourseSessionResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\CourseSessionResource\Pages\CreateCourseSession;
use App\Filament\Instructor\Resources\CourseSessionResource\Pages\EditCourseSession;
use App\Filament\Instructor\Resources\CourseSessionResource\Pages\ListCourseSessions;
use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\SessionRescheduledNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseSessionResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = CourseSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Session Timetable';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('Course')
                    ->options(fn (): array => static::instructorCourseOptions())
                    ->searchable()
                    ->required()
                    ->live(),

                Select::make('type')
                    ->options([
                        'group' => 'Group',
                        'one_on_one' => 'One-On-One',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('student_id', null)),

                Select::make('student_id')
                    ->label('Student')
                    ->options(function (callable $get): array {
                        $courseId = $get('course_id');

                        if (! $courseId) {
                            return User::query()->where('role', 'student')->pluck('name', 'id')->all();
                        }

                        return User::query()
                            ->whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId))
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->nullable()
                    ->visible(fn (callable $get): bool => $get('type') === 'one_on_one')
                    ->required(fn (callable $get): bool => $get('type') === 'one_on_one'),

                TextInput::make('title')
                    ->placeholder('e.g. Week 1: Introduction')
                    ->maxLength(255),

                DatePicker::make('session_date')
                    ->required(),

                TimePicker::make('start_time')
                    ->required()
                    ->seconds(false),

                TimePicker::make('end_time')
                    ->required()
                    ->seconds(false),

                Select::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'rescheduled' => 'Rescheduled',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('scheduled')
                    ->required()
                    ->reactive(),

                DatePicker::make('rescheduled_date')
                    ->visible(fn (callable $get): bool => $get('status') === 'rescheduled')
                    ->required(fn (callable $get): bool => $get('status') === 'rescheduled'),

                TimePicker::make('rescheduled_start_time')
                    ->seconds(false)
                    ->visible(fn (callable $get): bool => $get('status') === 'rescheduled')
                    ->required(fn (callable $get): bool => $get('status') === 'rescheduled'),

                TimePicker::make('rescheduled_end_time')
                    ->seconds(false)
                    ->visible(fn (callable $get): bool => $get('status') === 'rescheduled'),

                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'one_on_one' ? 'One-On-One' : 'Group')
                    ->color(fn (string $state): string => $state === 'group' ? 'success' : 'info'),
                TextColumn::make('student.name')
                    ->label('Student')
                    ->placeholder('All enrolled')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Session')
                    ->placeholder('—')
                    ->limit(30),
                TextColumn::make('session_date')
                    ->date('D, M j, Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->time('g:i A'),
                TextColumn::make('end_time')
                    ->time('g:i A'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'rescheduled' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('session_date', 'asc')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds()))
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'rescheduled' => 'Rescheduled',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'group' => 'Group',
                        'one_on_one' => 'One-On-One',
                    ]),
            ])
            ->recordActions([
                Action::make('markCompleted')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CourseSession $record): bool => $record->status === 'scheduled')
                    ->action(fn (CourseSession $record) => $record->update(['status' => 'completed'])),

                Action::make('reschedule')
                    ->label('Reschedule')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->visible(fn (CourseSession $record): bool => in_array($record->status, ['scheduled', 'rescheduled']))
                    ->form([
                        DatePicker::make('rescheduled_date')->required(),
                        TimePicker::make('rescheduled_start_time')->required()->seconds(false),
                        TimePicker::make('rescheduled_end_time')->seconds(false),
                    ])
                    ->action(function (CourseSession $record, array $data): void {
                        $record->update([
                            'status' => 'rescheduled',
                            'rescheduled_date' => $data['rescheduled_date'],
                            'rescheduled_start_time' => $data['rescheduled_start_time'],
                            'rescheduled_end_time' => $data['rescheduled_end_time'] ?? null,
                        ]);

                        $record->refresh();
                        $courseName = $record->course->title ?? 'Course';

                        if ($record->isOneOnOne() && $record->student_id) {
                            $student = User::find($record->student_id);
                            $student?->notify(new SessionRescheduledNotification($record, $courseName));
                        } else {
                            $students = User::query()
                                ->whereHas('enrollments', fn ($q) => $q->where('course_id', $record->course_id))
                                ->get();
                            foreach ($students as $student) {
                                $student->notify(new SessionRescheduledNotification($record, $courseName));
                            }
                        }
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseSessions::route('/'),
            'create' => CreateCourseSession::route('/create'),
            'edit' => EditCourseSession::route('/{record}/edit'),
        ];
    }
}
