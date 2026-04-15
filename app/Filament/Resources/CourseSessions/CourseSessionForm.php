<?php

namespace App\Filament\Resources\CourseSessions;

use App\Models\Course;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class CourseSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('Course')
                    ->options(Course::query()->where('is_active', true)->pluck('title', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('instructor_id')
                    ->label('Instructor')
                    ->options(User::query()->where('role', 'instructor')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),

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
                    ->options(fn (callable $get) => self::studentOptions($get('course_id')))
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

    private static function studentOptions(?string $courseId): array
    {
        if (! $courseId) {
            return User::query()->where('role', 'student')->pluck('name', 'id')->all();
        }

        return User::query()
            ->whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId))
            ->pluck('name', 'id')
            ->all();
    }
}
