<?php

namespace App\Filament\Resources\Assessments\Schemas;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssessmentForm
{
    public static function configure(Schema $schema): Schema
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
                    ->options(fn (): array => Course::query()->where('is_active', true)->orderBy('title')->pluck('title', 'id')->toArray())
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

                        return CourseIntake::query()
                            ->where('course_id', $courseId)
                            ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
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

                        $options = [
                            'all' => 'All Students',
                        ];

                        if (! $courseId || ! $level) {
                            return $options;
                        }

                        $students = User::query()
                            ->where(function ($query): void {
                                $query->whereNull('role')->orWhere('role', '!=', 'admin');
                            })
                            ->where('track', $level)
                            ->whereHas('courses', fn ($query) => $query->where('courses.id', $courseId))
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
}
