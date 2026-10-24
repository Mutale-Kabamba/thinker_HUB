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
                    ->live()
                    ->required(),

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
                    ->helperText('Leave empty to schedule for all cohorts, or pick a specific cohort.')
                    ->visible(fn (callable $get): bool => $get('type') !== 'one_on_one'),

                Select::make('instructor_id')
                    ->label('Instructor')
                    ->options(User::query()->whereIn('role', ['instructor', 'admin'])->orderBy('name')->pluck('name', 'id'))
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

                Select::make('delivery_mode')
                    ->label('Delivery Mode')
                    ->options(\App\Models\CourseSession::DELIVERY_MODES)
                    ->default(\App\Models\CourseSession::DELIVERY_LIVE_VIRTUAL)
                    ->required()
                    ->live(),

                TextInput::make('meeting_link')
                    ->label('Virtual Classroom Link (Google Meet, Zoom, etc.)')
                    ->placeholder('https://meet.google.com/...')
                    ->url()
                    ->visible(fn (callable $get): bool => ($get('delivery_mode') ?? \App\Models\CourseSession::DELIVERY_LIVE_VIRTUAL) === \App\Models\CourseSession::DELIVERY_LIVE_VIRTUAL)
                    ->required(fn (callable $get): bool => ($get('delivery_mode') ?? \App\Models\CourseSession::DELIVERY_LIVE_VIRTUAL) === \App\Models\CourseSession::DELIVERY_LIVE_VIRTUAL)
                    ->columnSpanFull(),

                TextInput::make('physical_address')
                    ->label('Physical Venue / Class Address')
                    ->placeholder('e.g. Thinker Hub Lab, Room 2B, Main Campus')
                    ->visible(fn (callable $get): bool => $get('delivery_mode') === \App\Models\CourseSession::DELIVERY_LIVE_PHYSICAL)
                    ->required(fn (callable $get): bool => $get('delivery_mode') === \App\Models\CourseSession::DELIVERY_LIVE_PHYSICAL)
                    ->columnSpanFull(),

                TextInput::make('video_url')
                    ->label('Video Guide / YouTube Link')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->url()
                    ->helperText('Paste a YouTube URL or direct video stream URL.')
                    ->visible(fn (callable $get): bool => $get('delivery_mode') === \App\Models\CourseSession::DELIVERY_VIDEO)
                    ->required(fn (callable $get): bool => $get('delivery_mode') === \App\Models\CourseSession::DELIVERY_VIDEO && empty($get('video_file_path')))
                    ->columnSpanFull(),

                \Filament\Forms\Components\FileUpload::make('video_file_path')
                    ->label('Or Upload Video File')
                    ->disk('public')
                    ->directory('session-videos')
                    ->visibility('public')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska'])
                    ->maxSize(102400) // 100MB
                    ->helperText('Upload an MP4 or WebM video file (optional alternative to link).')
                    ->visible(fn (callable $get): bool => $get('delivery_mode') === \App\Models\CourseSession::DELIVERY_VIDEO)
                    ->columnSpanFull(),

                \Filament\Forms\Components\FileUpload::make('reading_file_path')
                    ->label('Upload Reading Material (PDF, Doc, Slide)')
                    ->disk('public')
                    ->directory('session-readings')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])
                    ->maxSize(51200) // 50MB
                    ->helperText('PDF, Word document, or presentation slides.')
                    ->visible(fn (callable $get): bool => $get('delivery_mode') === \App\Models\CourseSession::DELIVERY_READ)
                    ->columnSpanFull(),

                Textarea::make('reading_content')
                    ->label('Or Written Article / Reading Guide Content')
                    ->placeholder('Write or paste the reading text or lesson content here...')
                    ->rows(6)
                    ->helperText('Markdown or plain text for students to read directly on screen.')
                    ->visible(fn (callable $get): bool => $get('delivery_mode') === \App\Models\CourseSession::DELIVERY_READ)
                    ->columnSpanFull(),

                TextInput::make('required_duration_minutes')
                    ->label('Required Time to Spend (Minutes)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(720)
                    ->default(15)
                    ->helperText('Time the student must spend actively engaging before attendance is marked as present.')
                    ->visible(fn (callable $get): bool => in_array($get('delivery_mode'), [\App\Models\CourseSession::DELIVERY_VIDEO, \App\Models\CourseSession::DELIVERY_READ], true))
                    ->required(fn (callable $get): bool => in_array($get('delivery_mode'), [\App\Models\CourseSession::DELIVERY_VIDEO, \App\Models\CourseSession::DELIVERY_READ], true)),

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
